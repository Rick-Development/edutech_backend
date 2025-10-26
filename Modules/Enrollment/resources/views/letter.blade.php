<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Letter</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #333; padding: 20px 0; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 24px; }
        .content { padding: 30px; line-height: 1.6; }
        .footer { text-align: center; margin-top: 40px; color: #777; font-size: 12px; }
        .highlight { background-color: #f9f9f9; padding: 15px; border-left: 3px solid #3498db; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>WAVECREST TRADING INSTITUTE</h1>
        <p>Edutech & Trading Education Platform</p>
    </div>

    <div class="content">
        <p><strong>Date:</strong> {{ $issue_date }}</p>

        <p>Dear {{ $student_name }},</p>

        <p>We are pleased to offer you admission into the <strong>{{ $course_title }}</strong> program at Wavecrest Trading Institute.</p>

        <div class="highlight">
            <p><strong>Matric Number:</strong> {{ $matric_number }}</p>
            <p><strong>Class Commencement Date:</strong> {{ $class_start_date }}</p>
        </div>

        <p>This letter serves as your official admission document. Please keep it for your records.</p>

        <p>Welcome to your trading journey!</p>

        <br><br>
        <p>Sincerely,<br>
        <strong>Admissions Office</strong><br>
        Wavecrest Trading Institute</p>
    </div>

    <div class="footer">
        <p>RC: 8442358 | 3rd Avenue, State Housing Estate, Calabar, Cross River State, Nigeria</p>
        <p>info@Wavecrest.com | +234 810 954 5948</p>
    </div>
</body>
</html>