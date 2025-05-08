<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Potvrda primitka poruke</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c3e50;">Potvrda primitka vaše poruke</h2>
        
        <p>Poštovani,</p>
        
        <p>Zahvaljujemo vam na vašoj poruci. Potvrđujemo da smo primili vašu poruku i odgovorit ćemo vam u najkraćem mogućem roku.</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Vaša poruka:</strong></p>
            <p>{{ $contactMessage }}</p>
        </div>
        
        <p>S poštovanjem,<br>
        ExpressLabelMaker tim</p>
        
        <hr style="border: 1px solid #eee; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #666;">
            Ova poruka je automatski generirana. Molimo ne odgovarajte na nju.
        </p>
    </div>
</body>
</html> 