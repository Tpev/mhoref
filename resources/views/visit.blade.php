<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Geriatric Round Optimization</title>
  <style>
    :root {
      --primary: #3498db;
      --accent: #2ecc71;
      --bg: #f4f8fb;
      --text: #2c3e50;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg);
      margin: 0;
      padding: 20px;
      color: var(--text);
    }

    .container {
      max-width: 960px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    h1 {
      color: var(--primary);
      font-size: 28px;
      margin-bottom: 20px;
    }

    .task-list {
      margin-bottom: 40px;
    }

    .task {
      background: #eaf6ff;
      border-left: 5px solid var(--primary);
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 25px;
    }

    .task h3 {
      margin-bottom: 10px;
      color: var(--primary);
    }

    .task small {
      display: block;
      margin-bottom: 10px;
      font-size: 13px;
      color: #7f8c8d;
    }

    .visit h4 {
      color: var(--accent);
      margin-bottom: 5px;
    }

    .visit ul {
      padding-left: 20px;
      margin: 0;
    }

    .visit li {
      margin-bottom: 6px;
    }

    .summary {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      font-size: 15px;
    }

    .summary h3 {
      margin-bottom: 10px;
      color: var(--primary);
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Geriatric MD Round Optimization Tool</h1>

    <div class="task-list">

      <!-- Patient 1 -->
      <div class="task">
        <h3>Patient: Louis Dupont</h3>
        <small>Room: 301 – Geriatric Unit<br>Age: 87 – CHF, Mild Dementia</small>

        <div class="visit">
          <h4>Visit Prior to Discharge</h4>
          <ul>
            <li>✅ Optimize medication list (stop unnecessary sedatives)</li>
            <li>✅ Confirm walker + bedside commode delivery (DME)</li>
            <li>✅ Simplify follow-up plan (single cardiology visit + PCP)</li>
            <li>✅ Discuss home safety and risk of falls with daughter</li>
          </ul>
        </div>
      </div>

      <!-- Patient 2 -->
      <div class="task">
        <h3>Patient: Maria Lopez</h3>
        <small>Room: 303 – Post-Hip Fracture Rehab<br>Age: 91 – Osteoporosis, Moderate Hearing Loss</small>

        <div class="visit">
          <h4>Visit Prior to Discharge</h4>
          <ul>
            <li>✅ Finalize DME: elevated toilet seat + PT plan at home</li>
            <li>✅ Assess home accessibility with OT input</li>
            <li>✅ Discontinue unnecessary anticoagulant</li>
            <li>✅ Educate family on fall prevention and return precautions</li>
          </ul>
        </div>
      </div>

      <!-- Patient 3 -->
      <div class="task">
        <h3>Patient: Georges Martin</h3>
        <small>Room: 305 – Post-Stroke<br>Age: 85 – Aphasia, High caregiver burden</small>

        <div class="visit">
          <h4>Visit Prior to Discharge</h4>
          <ul>
            <li>✅ Review feeding assistance needs with RN + family</li>
            <li>✅ Ensure DME: hospital bed and transfer board approved</li>
            <li>✅ Clarify home nursing frequency with social worker</li>
            <li>✅ Evaluate decision-making capacity and update POLST</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="summary">
      <h3>Daily Optimization Summary</h3>
      <ul>
        <li>🧠 Medication streamlined for 3 patients</li>
        <li>🏡 Home safety and caregiver instructions completed</li>
        <li>📦 DME confirmed for all discharges</li>
        <li>📞 Care transitions simplified and coordinated with primary care</li>
      </ul>
    </div>
  </div>

</body>
</html>
