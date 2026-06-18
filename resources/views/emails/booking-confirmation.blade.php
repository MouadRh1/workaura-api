<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0A0A0F;
            color: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
            background: #12121A;
            border-radius: 20px;
            border: 1px solid #2A2A3E;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #2A2A3E;
        }
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #F4620A, #C040E0);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 24px;
            font-weight: bold;
            color: white;
        }
        .content {
            padding: 30px 0;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(74, 222, 128, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #A0A0B8;
            margin-bottom: 30px;
        }
        .details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #A0A0B8;
        }
        .detail-value {
            font-weight: 600;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #F4620A, #C040E0);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            text-align: center;
            margin: 20px auto;
        }
        .footer {
            text-align: center;
            color: #A0A0B8;
            font-size: 12px;
            border-top: 1px solid #2A2A3E;
            padding-top: 20px;
            margin-top: 20px;
        }
        .highlight {
            color: #F4620A;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">WA</div>
            <h1 style="margin: 0;">WORKAURA</h1>
            <p style="color: #A0A0B8; margin: 0; font-size: 14px;">Working Space</p>
        </div>

        <div class="content">
            <div class="success-icon">✅</div>
            <h1>Réservation confirmée !</h1>
            <p class="subtitle">Votre réservation a été créée avec succès</p>

            <div class="details">
                <h3 style="color: #F4620A; margin-top: 0;">📋 Détails de la réservation</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Espace</span>
                    <span class="detail-value">{{ $booking->space->name }}</span>
                </div>
                
                @if($booking->room_size)
                <div class="detail-row">
                    <span class="detail-label">Taille de salle</span>
                    <span class="detail-value">{{ $booking->room_size === 'small' ? 'Petite salle' : 'Grande salle' }}</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
                </div>
                
                @if($booking->start_time && $booking->end_time)
                <div class="detail-row">
                    <span class="detail-label">Horaire</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</span>
                </div>
                @endif
                
                @if($booking->end_date)
                <div class="detail-row">
                    <span class="detail-label">Période</span>
                    <span class="detail-value">Du {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') }}</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Durée</span>
                    <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $booking->duration_type)) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Montant total</span>
                    <span class="detail-value" style="color: #F4620A; font-size: 18px;">{{ number_format($booking->total_amount, 0, ',', ' ') }} MAD</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value" style="color: #FBBF24;">⏳ En attente de confirmation</span>
                </div>
            </div>

            <div style="text-align: center; background: rgba(244, 98, 10, 0.1); border-radius: 12px; padding: 15px; margin: 20px 0; border: 1px solid rgba(244, 98, 10, 0.2);">
                <p style="margin: 0; color: #A0A0B8; font-size: 14px;">
                    🔑 Votre token de réservation
                </p>
                <p style="margin: 5px 0 0; font-size: 12px; font-family: monospace; color: #F4620A; word-break: break-all;">
                    {{ $token }}
                </p>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/api/bookings/' . $token . '/confirm') }}" class="button">
                    ✅ Confirmer ma réservation
                </a>
            </div>

            <div style="background: rgba(251, 191, 36, 0.1); border-radius: 12px; padding: 15px; margin: 20px 0; border: 1px solid rgba(251, 191, 36, 0.2);">
                <p style="margin: 0; color: #FBBF24; font-size: 14px;">
                    ⚠️ Ce lien de confirmation est valable 48h.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>
                Workaura - Espace de coworking à Témara<br>
                📍 Avenue Mohammed V, Témara, Maroc<br>
                📞 +212 6 00 00 00 00 • ✉️ contact@workaura.ma
            </p>
            <p style="margin-top: 10px; font-size: 11px;">
                © {{ date('Y') }} Workaura. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>