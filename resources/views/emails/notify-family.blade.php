<!DOCTYPE html>
<html>
<head>
    <title>Patient Discharge Notification</title>
</head>
<body>
    <h1>Dear {{ $familyName }},</h1>

    <p>
      We are writing to inform you that {{ $patientName }} is scheduled for discharge on {{ $dischargeDate }}.
    </p>

    <p>
      Please contact us if you have any questions or concerns.
    </p>

    <p>
      Sincerely,<br>
      The Discharge Team
    </p>
</body>
</html>
