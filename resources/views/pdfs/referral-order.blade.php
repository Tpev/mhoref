<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    /*  ───── Page & base font ───── */
    @page   { margin: 15mm 12mm 20mm 12mm; size: letter; }
    body    { font-family: Arial, Helvetica, sans-serif; font-size: 11px; }
    .title  { font-size: 17px; font-weight: 700; text-align: center; margin-bottom: 4mm; }

    /*  ───── Meta table ───── */
    table.meta     { width:100%; margin-bottom:3mm; }
    table.meta td  { padding:1.2mm 0; vertical-align:top; }
    table.meta td:first-child { width:32mm; font-weight:700; }

    /*  ───── Section heading ───── */
    .sec-hd { background:#e5e5e5; font-weight:700; padding:1.2mm 1mm;
              margin:1.8mm 0 1.2mm; border:1px solid #888; }

    /*  ───── 3-column checkbox grid ───── */
    .chk-tri    { width:100%; border-collapse:collapse; }
    .chk-tri td { width:33%; padding:0.8mm 0; }

    /*  ───── Signature box ───── */
    .sig-box { margin-top:6mm; border-top:1px solid #777; padding-top:2mm; }

    /*  ───── Checkbox glyphs ───── */
.chk      { font-family:"DejaVu Sans", sans-serif; }
.chk.on   { font-size:15px; color:#000; }            /* ☑ bigger, darker */
    /*  ───── POWERED-BY footer ───── */
    .pdf-footer{
        position:fixed;       /* ⇠ detach from normal flow            */
        bottom:0; left:0; right:0;
        text-align:center;    /* centre across whole page             */
        font-size:9px; color:#555;
    }
    .pdf-footer img{
        height:15px;          /* small logo                           */
        margin-left:4px;
        vertical-align:middle;
    }
}

</style>
</head>
<body>

{{-- ═══ helper (one instance) ═══ --}}
@php
    /* snake-case every incoming key once */
    $norm = collect($data)->mapWithKeys(
        fn($v,$k)=>[strtolower(preg_replace('/[^a-z0-9]+/','_', $k))=>$v]
    );

    /* print ☑ bold or ☐ thin */
    $ck = fn($key) => !empty($norm[$key])
        ? '<span class="chk on">&#x2611;</span>'   /* checked */
        : '<span class="chk">&#x2610;</span>';     /* unchecked */
@endphp

{{-- ─────────── Header ─────────── --}}
<div class="title">Detailed Written Order</div>

<table class="meta">
<tr><td>Date of Order:</td><td>{{ $data['date_of_order'] ?? '—' }}</td>
    <td style="width:20mm;"></td>
    <td>Patient Name:</td><td>{{ $data['patient_name'] ?? '—' }}</td></tr>
<tr><td>Height:</td>  <td>{{ $data['height']  ?? '—' }}</td>
    <td></td>
    <td>Weight:</td>  <td>{{ $data['weight']  ?? '—' }}</td></tr>
<tr><td>Length of need:</td><td>{{ $data['length_of_need'] ?? '—' }}</td>
    <td></td>
    <td>Diagnosis:</td>      <td>{{ $data['diagnosis'] ?? '—' }}</td></tr>
</table>

{{-- ───────── Durable Medical Equipment ───────── --}}
<div class="sec-hd">DURABLE MEDICAL EQUIPMENT</div>
<table class="chk-tri">
<tr><td>{!! $ck('walker_adult_wheels')        !!} Adult w/ Wheels (E0143)</td>
    <td>{!! $ck('walker_straight_cane')       !!} Straight Cane (E0100)</td>
    <td>{!! $ck('shower_back')                !!} Shower Chair w/ Back</td></tr>
<tr><td>{!! $ck('walker_junior_wheels')       !!} Junior w/ Wheels (E0143)</td>
    <td>{!! $ck('walker_quad_cane')           !!} Quad Cane (E0105)</td>
    <td>{!! $ck('shower_no_back')             !!} Shower Chair w/out Back</td></tr>
<tr><td>{!! $ck('walker_heavy_duty_wheels')   !!} Heavy Duty w/ Wheels (E0149)</td>
    <td>{!! $ck('commode_standard')           !!} Bedside Commode (E0163)</td>
    <td>{!! $ck('tub_bench')                  !!} Tub Transfer Bench</td></tr>
<tr><td>{!! $ck('walker_platform_attachment') !!} Platform Attachment (E0154)</td>
    <td>{!! $ck('commode_hd')                 !!} HD Bedside Commode (E0168)</td>
    <td>{!! $ck('patient_lift')               !!} Patient Lift (E0630)</td></tr>
<tr><td>{!! $ck('walker_rollator')            !!} Rollator (E0143/56)</td>
    <td>{!! $ck('commode_drop_arm')           !!} Drop-Arm Commode (E0165)</td>
    <td>{!! $ck('nebulizer_comp')             !!} Nebulizer Compressor</td></tr>
<tr><td>{!! $ck('walker_hd_rollator')         !!} HD Rollator (E0149/56)</td>
    <td>{!! $ck('commode_hd_drop')            !!} HD Drop-Arm Commode</td>
    <td>{!! $ck('nebulizer_kits')             !!} Nebulizer Kits</td></tr>
</table>

{{-- ───────── Oxygen ───────── --}}
<div class="sec-hd">OXYGEN</div>
<table class="chk-tri">
<tr><td>{!! $ck('oxygen_concentrator') !!} Concentrator (E1390)</td>
    <td>O<sub>2</sub> LPM: {{ $data['oxygen_lpm'] ?? '—' }}</td>
    <td>Admin: {{ $data['oxygen_admin'] ?? '—' }}</td></tr>
<tr><td>{!! $ck('oxygen_portable') !!} Portable Concentrator</td>
    <td>Frequency: {{ $data['oxygen_freq'] ?? '—' }}</td>
    <td></td></tr>
<tr><td>{!! $ck('oxygen_homefill') !!} Homefill System</td>
    <td>{!! $ck('oxygen_conserv') !!} Conserving Device</td>
    <td>{!! $ck('oxygen_tanks')   !!} Portable Tanks</td></tr>
</table>

{{-- ───────── Wheelchairs ───────── --}}
<div class="sec-hd">WHEELCHAIRS</div>
<table class="chk-tri">
<tr><td>{!! $ck('wheelchair_standard') !!} Standard (K0001)</td>
    <td>{!! $ck('wc_cushion') !!} Seat Cushion</td>
    <td>{!! $ck('wc_solid_insert') !!} Solid Seat Insert</td></tr>
<tr><td>{!! $ck('wheelchair_hemi') !!} Hemi (K0002)</td>
    <td>{!! $ck('wc_back_cushion') !!} Back Cushion</td>
    <td>{!! $ck('wc_transfer_board') !!} Transfer Board</td></tr>
<tr><td>{!! $ck('wc_light') !!} Lightweight (K0003)</td>
    <td>{!! $ck('wc_leg_rest') !!} Elevating Leg Rest</td>
    <td>{!! $ck('wc_arm_trough') !!} Arm Trough L/R</td></tr>
<tr><td>{!! $ck('wc_ultralight') !!} Ultra Lightweight</td>
    <td>{!! $ck('wc_antitippers') !!} Anti-Tippers</td>
    <td>{!! $ck('wc_lap_tray') !!} Lap Tray</td></tr>
<tr><td>{!! $ck('wc_recliner') !!} Recliner</td>
    <td>{!! $ck('wc_arm_rest') !!} Adj. Arm Rest</td>
    <td>{!! $ck('wc_travel_chair') !!} Travel Chair</td></tr>
<tr><td>{!! $ck('wc_hd') !!} Heavy Duty</td>
    <td>{!! $ck('wc_safety_belt') !!} Safety Belt</td>
    <td>{!! $ck('wc_brake_ext') !!} Brake Extensions</td></tr>
<tr><td>{!! $ck('wc_xhd') !!} Extra Heavy Duty</td>
    <td>{!! $ck('wc_amputee') !!} Amputee Pad</td>
    <td></td></tr>
</table>

{{-- ───────── Hospital Beds ───────── --}}
<div class="sec-hd">HOSPITAL BEDS</div>
<table class="chk-tri">
<tr><td>{!! $ck('hb_manual') !!} Manual (E0255)</td>
    <td>{!! $ck('mattress_gel') !!} Gel Overlay</td>
    <td>{!! $ck('ba_trapeze') !!} Trapeze Bar</td></tr>
<tr><td>{!! $ck('hb_semi') !!} Semi-Electric (E0260)</td>
    <td>{!! $ck('mattress_pressure') !!} Alt. Pressure Pad</td>
    <td></td></tr>
<tr><td>{!! $ck('hb_hd') !!} Heavy Duty (E0303)</td>
    <td>{!! $ck('mattress_low_air') !!} Low Air Loss Mattress</td>
    <td></td></tr>
</table>

@if(!empty($data['seating_eval']))
<p style="margin-top:2mm;">☑ Seating evaluation for power wheelchair or custom manual wheelchair</p>
@endif
@if(!empty($data['other_items']))
<p style="margin-top:2mm;"><strong>Other Items:</strong> {{ $data['other_items'] }}</p>
@endif

{{-- ───────── Physician box ───────── --}}
<div class="sig-box">
<table style="width:100%;">
<tr><td style="width:37mm;"><strong>Physician Name:</strong></td>
    <td style="border-bottom:1px solid #777;">{{ $data['physician_name'] ?? '' }}</td>
    <td style="width:10mm;"></td>
    <td style="width:12mm;"><strong>NPI #:</strong></td>
    <td style="border-bottom:1px solid #777;">{{ $data['physician_npi'] ?? '' }}</td></tr>
<tr><td style="padding-top:3mm;"><strong>Physician Signature:</strong></td>
    <td style="border-bottom:1px solid #777; height:24mm;">
        @if(!empty($signaturePath))
            <img src="{{ $signaturePath }}" style="height:22mm;">
        @endif
    </td>
    <td></td>
    <td><strong>Date:</strong></td>
    <td style="border-bottom:1px solid #777;">{{ $data['signature_date'] ?? '' }}</td></tr>
</table>
<p style="margin-top:2mm; font-size:9px;">
    As the ordering physician, I attest to the evaluation, assessment, treatment and follow-up in relation to both the patient and prescribed equipment above.
</p>
</div>
{{-- ─── Footer ─── --}}
<div class="pdf-footer">
    Powered by
    <img src="{{ public_path('images/logo-gray.png') }}" alt="Logo">
</div>
</body>
</html>
