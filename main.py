# main.py
import io
import re
from typing import List, Dict, Any, Optional

import fitz  # PyMuPDF
from PIL import Image
import pytesseract

from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.responses import JSONResponse

app = FastAPI(title="Referral OCR API", version="0.3.4")

# If Tesseract isn't in PATH on Windows, uncomment and set:
# pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"


# -------------------------
# Utilities
# -------------------------
def pdf_to_images(pdf_bytes: bytes, zoom: float = 2.0) -> List[Image.Image]:
    images: List[Image.Image] = []
    with fitz.open("pdf", pdf_bytes) as doc:
        mat = fitz.Matrix(zoom, zoom)
        for page in doc:
            pix = page.get_pixmap(matrix=mat, alpha=False)
            img = Image.open(io.BytesIO(pix.tobytes("png"))).convert("RGB")
            images.append(img)
    return images


def _to_float_conf(v) -> float:
    try:
        return float(v)
    except (TypeError, ValueError):
        return -1.0


def ocr_page(image: Image.Image) -> List[Dict[str, Any]]:
    data = pytesseract.image_to_data(image, output_type=pytesseract.Output.DICT)
    n = len(data.get("text", []))

    lines = {}
    for i in range(n):
        text = (data["text"][i] or "").strip()
        if not text:
            continue

        conf100 = _to_float_conf(data["conf"][i])
        key = (data["block_num"][i], data["par_num"][i], data["line_num"][i])

        entry = lines.setdefault(key, {
            "line_num": int(data["line_num"][i]),
            "text": [],
            "confs": [],
            "left": [],
            "top": [],
            "width": [],
            "height": [],
        })
        entry["text"].append(text)
        entry["confs"].append(conf100)
        entry["left"].append(int(data["left"][i]))
        entry["top"].append(int(data["top"][i]))
        entry["width"].append(int(data["width"][i]))
        entry["height"].append(int(data["height"][i]))

    out = []
    for entry in lines.values():
        words = entry["text"]
        confs = [c for c in entry["confs"] if c >= 0]
        avg_conf = (sum(confs) / len(confs) / 100.0) if confs else 0.5  # 0..1

        x1 = min(entry["left"])
        y1 = min(entry["top"])
        x2 = max(l + w for l, w in zip(entry["left"], entry["width"]))
        y2 = max(t + h for t, h in zip(entry["top"], entry["height"]))

        out.append({
            "line_num": entry["line_num"],
            "text": " ".join(words),
            "conf": round(avg_conf, 3),
            "bbox": (x1, y1, x2, y2),
            "words": list(zip(words, entry["confs"])),  # still 0..100 per word
        })

    out.sort(key=lambda d: d["line_num"])
    return out


# -------------------------
# Extraction logic
# -------------------------

# Labels we NEVER want inside names
LABEL_TOKENS = r"(?:DOB|D\.?O\.?B\.?|MRN|Gender|Sex|Age|Date|Phone|Account|ID|Number|Member|Policy|Plan|Insured|Subscriber|Employer)"

DOB_PATTERNS = [
    r"(?:DOB|Date of Birth)\s*[:\-]?\s*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})",
    r"\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\b",  # fallback
]

# 1) LAST, First  (allow an extra given token unless it's a label)
PAT_LAST_FIRST = re.compile(
    rf"\b([A-Z][A-Z' -]+),\s*([A-Z][A-Za-z' -]+)"
    rf"(?:\s+(?!(?:{LABEL_TOKENS})\b)[A-Z][A-Za-z' -]+)?\b"
)

# 2) First (Middle?) Last  (block label tokens in BOTH the middle and last positions)
PAT_FIRST_LAST = re.compile(
    rf"\b("
        rf"[A-Z][A-Za-z' -]+"                               # First
        rf"(?:\s+(?!(?:{LABEL_TOKENS})\b)[A-Z][A-Za-z' -]+)?" # optional Middle (not a label)
    rf")\s+"
    rf"((?!(?:{LABEL_TOKENS})\b)[A-Z][A-Za-z' -]+)\b"        # Last (not a label)
)

# Lines like "Subscriber Name: KENT, CLARK" (label-aware boost)
NAME_LABELS = re.compile(
    r"(?:^|\b)(Subscriber Name|Patient Name|Member Name|Insured Name)\b\s*:",
    re.IGNORECASE
)

def contains_label_token(s: str) -> bool:
    return bool(re.search(rf"\b{LABEL_TOKENS}\b", s, re.IGNORECASE))


def smart_title(s: str) -> str:
    return s.title() if s.isupper() else s


def score(base: float, line_conf_0_1: float, boosts: float = 0.0) -> float:
    s = base * (0.6 + 0.4 * line_conf_0_1) + boosts
    return float(max(0.0, min(0.99, round(s, 3))))


def extract_dobs(lines: List[Dict[str, Any]], template: str, page_idx: int) -> List[Dict[str, Any]]:
    results = []
    for li, line in enumerate(lines, start=1):
        txt = line["text"]
        for pat in DOB_PATTERNS:
            m = re.search(pat, txt, flags=re.IGNORECASE)
            if not m:
                continue
            date = m.group(1)
            boost = 0.2 if re.search(r"(DOB|Date of Birth)", txt, re.I) else 0.0
            if template.lower().startswith("athena") and "dob" in txt.lower():
                boost += 0.1
            conf = score(0.7, line["conf"], boosts=boost)
            results.append({
                "date": date,
                "confidence": conf,
                "page": page_idx + 1,
                "line": li,
                "context": txt,
            })
    return results


def extract_people(lines: List[Dict[str, Any]], template: str, page_idx: int) -> List[Dict[str, Any]]:
    results = []
    tmpl = (template or "").lower()

    for li, line in enumerate(lines, start=1):
        txt = line["text"]
        has_label = bool(NAME_LABELS.search(txt))

        # 1) LAST, First (e.g. "BOND, James DOB: ...") – avoid swallowing labels
        for m in PAT_LAST_FIRST.finditer(txt):
            last = m.group(1).strip(" ,")
            first = m.group(2).strip(" ,")

            # hard guard: reject if labels slipped in anyway
            if contains_label_token(first) or contains_label_token(last):
                continue
            if len(first) < 2 or len(last) < 2:
                continue

            boost = 0.0
            if tmpl.startswith("athena"):
                boost += 0.15
                if "dob" in txt.lower():
                    boost += 0.10
            if has_label:
                boost += 0.25  # Intermed-style label present

            results.append({
                "first_name": smart_title(first),
                "last_name": smart_title(last),
                "confidence": score(0.78, line["conf"], boosts=boost),
                "page": page_idx + 1,
                "line": li,
                "source": "last_first",
                "context": txt,
            })

        # 2) First (Middle?) Last (also label-guarded)
        for m in PAT_FIRST_LAST.finditer(txt):
            first = m.group(1).strip(" ,")
            last = m.group(2).strip(" ,")

            if contains_label_token(first) or contains_label_token(last):
                continue
            if len(first) < 2 or len(last) < 2:
                continue

            bad = {"note", "summary", "referral", "order", "provider", "medicaid", "dob", "mrn"}
            if first.lower() in bad or last.lower() in bad:
                continue

            boost = 0.0
            if has_label:
                boost += 0.25
            if tmpl in ("intermed", "northern light", "maine general"):
                boost += 0.05

            results.append({
                "first_name": smart_title(first),
                "last_name": smart_title(last),
                "confidence": score(0.66, line["conf"], boosts=boost),
                "page": page_idx + 1,
                "line": li,
                "source": "first_last",
                "context": txt,
            })

    return results


def template_cleanup(lines: List[Dict[str, Any]], template: str) -> List[Dict[str, Any]]:
    out = []
    tmpl = (template or "").lower()
    for ln in lines:
        t = ln["text"].strip()

        if tmpl.startswith("athena"):
            if "athenahealth" in t.lower() or "this fax may contain" in t.lower():
                continue
            if re.search(r"\bPage:\s*\d+\/\d+\b", t, re.I):
                continue

        if tmpl == "northern light" and "northern light" in t.lower():
            continue

        if tmpl == "maine general" and "maine general" in t.lower():
            continue

        out.append(ln)
    return out


# -------------------------
# API
# -------------------------
@app.post("/extract")
async def extract(
    file: UploadFile = File(...),
    template: Optional[str] = Form("Athena")
):
    if file.content_type not in ("application/pdf", "application/octet-stream"):
        raise HTTPException(status_code=415, detail="Please upload a PDF.")

    pdf_bytes = await file.read()

    try:
        images = pdf_to_images(pdf_bytes, zoom=2.0)
        if not images:
            raise HTTPException(status_code=422, detail="No pages rendered.")

        all_people: List[Dict[str, Any]] = []
        all_dobs: List[Dict[str, Any]] = []

        for page_idx, img in enumerate(images):
            lines = ocr_page(img)
            lines = template_cleanup(lines, template or "")
            all_dobs.extend(extract_dobs(lines, template or "", page_idx))
            all_people.extend(extract_people(lines, template or "", page_idx))

        # De-duplicate people by (first,last)
        seen = set()
        dedup_people = []
        for p in sorted(all_people, key=lambda r: r["confidence"], reverse=True):
            key = (p["first_name"].strip().lower(), p["last_name"].strip().lower())
            if key in seen:
                continue
            seen.add(key)
            dedup_people.append(p)

        # De-duplicate DOBs by exact date string
        seen_d = set()
        dedup_dobs = []
        for d in sorted(all_dobs, key=lambda r: r["confidence"], reverse=True):
            if d["date"] in seen_d:
                continue
            seen_d.add(d["date"])
            dedup_dobs.append(d)

        result = {
            "template": template or "Unknown",
            "pages": len(images),
            "people": dedup_people,
            "dobs": dedup_dobs,
        }
        return JSONResponse(result)

    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Processing error: {e}")


@app.get("/")
def root():
    return {"ok": True, "service": "Referral OCR API", "version": "0.3.4"}
