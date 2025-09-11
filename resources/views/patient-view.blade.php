<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Patient Family Portal – MaineHealth</title>
  <style>
    :root {
      --primary: #2ecc71;
      --primary-dark: #27ae60;
      --text: #2c3e50;
      --bg: #f4f6f5;
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

    h1, h2, h3 {
      color: var(--primary-dark);
    }

    .patient-info, .questions {
      margin-bottom: 40px;
    }

    .patient-info p {
      margin: 8px 0;
    }

    .timeline {
      border-left: 4px solid var(--primary);
      padding-left: 25px;
      position: relative;
    }

    .event {
      margin-bottom: 30px;
      position: relative;
      padding-left: 15px;
    }

    .event::before {
      content: '';
      position: absolute;
      left: -31px;
      top: 3px;
      width: 18px;
      height: 18px;
      background: var(--primary);
      border-radius: 50%;
      border: 3px solid #fff;
      box-shadow: 0 0 0 2px var(--primary);
    }

    .event h3 {
      margin-bottom: 5px;
    }

    .event small {
      color: #777;
    }

    .event p {
      margin-top: 5px;
      font-size: 15px;
    }

    .questions textarea,
    .comment textarea {
      width: 100%;
      height: 100px;
      margin-top: 10px;
      padding: 10px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .questions button,
    .comment button {
      background: var(--primary);
      color: white;
      padding: 12px 24px;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      margin-top: 10px;
      cursor: pointer;
      transition: 0.2s ease;
    }

    .questions button:hover,
    .comment button:hover {
      background: var(--primary-dark);
    }

    .notification {
      background: #eafaf1;
      border-left: 5px solid var(--primary-dark);
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 30px;
    }

    .comment-section {
      margin-top: 60px;
    }

    .comment-section h2 {
      margin-bottom: 10px;
    }

    .existing-comments {
      margin-top: 30px;
    }

    .comment-item {
      border-top: 1px solid #eee;
      padding-top: 15px;
      margin-top: 15px;
    }

    .comment-item strong {
      color: var(--primary-dark);
    }

    .comment-item p {
      margin: 5px 0 0;
    }

    @media (max-width: 600px) {
      .container {
        padding: 20px;
      }
    }
	.comment-item.reply {
  background: #f7fcf9;
  padding: 12px 16px;
  border-left: 4px solid var(--primary-dark);
  border-radius: 6px;
  margin-top: 10px;
}

  </style>
</head>
<body>

  <div class="container">
    <h1>Family Communication Portal</h1>

    <div class="patient-info">
      <h2>Patient Details</h2>
      <p><strong>Patient Name:</strong> Margaret Thompson</p>
      <p><strong>Age:</strong> 86</p>
      <p><strong>Room:</strong> 208A – Skilled Nursing Unit</p>
      <p><strong>Admission Date:</strong> April 9, 2025</p>
      <p><strong>Primary Provider:</strong> Dr. Samuel Greene</p>
    </div>

    <div class="notification">
      <strong>Upcoming:</strong> A discharge is being evaluated for <strong>April 20</strong>. A walker and home health nursing are being coordinated.
    </div>

    <h2>Care Timeline</h2>
    <div class="timeline">

      <div class="event">
        <h3>Admission & Initial Assessment</h3>
        <small>April 9 – 3:45 PM</small>
        <p>Ms. Thompson was admitted after a fall at home. Initial nursing and rehab evaluations completed. Fall risk protocols initiated.</p>
      </div>

      <div class="event">
        <h3>Physical & Occupational Therapy Evaluation</h3>
        <small>April 10</small>
        <p>Therapists assessed mobility, ADLs, and home setup. Moderate assistance currently required for transfers and walking.</p>
      </div>

      <div class="event">
        <h3>Medication Review</h3>
        <small>April 11</small>
        <p>Medication list reviewed by clinical pharmacist. Adjustments made to antihypertensive regimen due to recent hypotension.</p>
      </div>

      <div class="event">
        <h3>Family Meeting</h3>
        <small>April 13 – via phone</small>
        <p>Spoke with daughter, Linda Thompson. Discussed rehab progress, likely discharge options, and equipment needs.</p>
      </div>

      <div class="event">
        <h3>DME Order Placed</h3>
        <small>April 15</small>
        <p>Walker, shower chair, and bedside commode ordered. Delivery to patient’s home scheduled for April 19.</p>
      </div>

      <div class="event">
        <h3>Discharge Planning Begins</h3>
        <small>April 17</small>
        <p>Social work coordinating with family and agency for skilled home health nursing post-discharge.</p>
      </div>

      <div class="event">
        <h3>Next Step: Final Rehab Evaluation</h3>
        <small>Planned: April 19</small>
        <p>Therapy to re-evaluate safe mobility with walker and provide family training if needed.</p>
      </div>
    </div>



<div class="comment-section">
  <h2>Family & Provider Communication</h2>

  <form class="comment">
    <textarea placeholder="Ask a question or share an update..."></textarea>
    <br />
    <button type="submit">Post Comment</button>
  </form>

  <div class="existing-comments">

    <div class="comment-item">
      <strong style="color: #2c3e50;">Linda Thompson <span style="font-weight: normal; color: #888;">(Family)</span></strong>
      <p>Thank you so much for the detailed updates! Very reassuring to know everything is being handled so thoroughly.</p>
    </div>

    <div class="comment-item reply">
      <strong style="color: #27ae60;">Nurse Julia Martin <span style="font-weight: normal; color: #888;">(Provider)</span></strong>
      <p>You're very welcome, Linda. Margaret is making steady progress, and we’ll continue to keep you updated as we approach discharge planning.</p>
    </div>

    <div class="comment-item">
      <strong style="color: #2c3e50;">David Thompson <span style="font-weight: normal; color: #888;">(Family)</span></strong>
      <p>Will someone be home to receive the walker and shower chair on the 19th? I can take the day off if needed.</p>
    </div>

    <div class="comment-item reply">
      <strong style="color: #27ae60;">Social Worker Amy Chen <span style="font-weight: normal; color: #888;">(Provider)</span></strong>
      <p>Hi David — thanks for checking! Delivery is scheduled between 11am–3pm. If someone can be available at home during that window, that would be perfect.</p>
    </div>

  </div>
</div>


  </div>

</body>
</html>
