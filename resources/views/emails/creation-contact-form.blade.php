<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande de création - Pensée Bohème</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .content p {
            text-align: center;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
        .value {
            margin-top: 5px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nouvelle demande de création personnalisée de {{ $data['firstName'] }} {{ $data['lastName'] }}</h1>
        <p>Pensée Bohème</p>
    </div>

    <div class="content">
        <p>Vous avez reçu une nouvelle demande de création personnalisée de {{ $data['firstName'] }} {{ $data['lastName'] }} .</p>

        <div class="field">
            <div class="label">Prénom :</div>
            <div class="value">{{ $data['firstName'] }}</div>
        </div>

        <div class="field">
            <div class="label">Nom :</div>
            <div class="value">{{ $data['lastName'] }}</div>
        </div>

        <div class="field">
            <div class="label">Email :</div>
            <div class="value">{{ $data['email'] }}</div>
        </div>

        <div class="field">
            <div class="label">Téléphone :</div>
            <div class="value">{{ $data['phone'] }}</div>
        </div>

        <div class="field">
            <div class="label">Message :</div>
            <div class="value">{{ $data['message'] }}</div>
        </div>

        <hr style="margin: 20px 0; border: 1px solid #e9ecef;">
        <p><small>Reçu le {{ now()->format('d/m/Y à H:i') }}</small></p>
    </div>
</body>
</html>