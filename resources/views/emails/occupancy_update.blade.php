<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Update Okupansi</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 40px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .email-header {
            background-color: #dc2626; /* Warna merah */
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 32px;
            color: #333333;
        }
        .email-body p {
            margin: 0 0 16px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            margin-bottom: 24px;
        }
        .details-table th, .details-table td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        .details-table th {
            background-color: #f8fafc;
            width: 40%;
            font-weight: 600;
        }
        .cta-button {
            display: inline-block;
            background-color: #dc2626; /* Warna merah */
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 16px;
        }
        .email-footer {
            background-color: #f1f5f9;
            color: #64748b;
            padding: 24px;
            text-align: center;
            font-size: 12px;
        }
        .email-footer a {
            color: #dc2626;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>Update Okupansi Properti</h1>
            </div>
            <div class="email-body">
                <p>Halo Tim Ecommerce,</p>
                <p>
                    Sistem telah mencatat adanya pembaruan data okupansi untuk properti 
                    <strong>{{ $property->name }}</strong> pada tanggal
                    <strong>{{ \Carbon\Carbon::parse($occupancy->date)->translatedFormat('l, d F Y') }}</strong>.
                </p>
                
                <table class="details-table">
                    <tr>
                        <th>Total Kamar Terisi</th>
                        <td><strong>{{ $occupancy->occupied_rooms }}</strong></td>
                    </tr>
                    <tr>
                        <th>Dari Reservasi OTA</th>
                        <td>{{ $occupancy->reservasi_ota }}</td>
                    </tr>
                    <tr>
                        <th>Dari Input Properti (Manual)</th>
                        <td>{{ $occupancy->reservasi_properti }}</td>
                    </tr>
                </table>

                <p>
                    Anda dapat melihat detail lebih lanjut dan pengaruhnya terhadap harga BAR dengan menekan tombol di bawah ini.
                </p>

                <a href="{{ route('ecommerce.dashboard', ['property_id' => $property->id]) }}" class="cta-button">
                    Lihat Dasbor
                </a>
            </div>
            <div class="email-footer">
                <p>Ini adalah email otomatis. Mohon tidak membalas email ini.</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>