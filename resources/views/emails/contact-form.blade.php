<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nova kontakt poruka</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #045cb8;">Nova kontakt poruka</h2>
        
        <p><strong>Email:</strong> {{ $email }}</p>
        
        <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <p>{!! nl2br(e($contactMessage)) !!}</p>
        </div>

        <p style="color: #666; font-size: 12px; margin-top: 30px;">
            Ova poruka je poslana putem kontakt forme na ExpressLabelMaker.com
        </p>
    </div>
</body>
</html> 