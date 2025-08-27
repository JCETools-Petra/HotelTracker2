<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - {{ $booking->booking_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header, .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }
        .header {
            top: 0px;
        }
        .footer {
            bottom: 0px;
            font-size: 10px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .document-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            text-decoration: underline;
        }
        .info-table td {
            border: none;
            padding: 5px;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-section table {
            width: 40%;
            float: right;
        }
        .notes-terms {
            margin-top: 40px;
        }
        .signature-section {
            margin-top: 80px;
            width: 100%;
        }
        .signature {
            width: 30%;
            float: left;
            text-align: center;
        }
        .signature-space {
            height: 80px;
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <table style="border: none;">
            <tr>
                <td style="width: 70%; border: none;">
                    <h1 style="margin: 0;">{{ $booking->property->name }}</h1>
                    <p style="margin: 0;">{{ $booking->property->address ?? 'Alamat properti belum diatur' }}</p>
                </td>
                <td style="width: 30%; text-align: right; border: none;">
                    {{-- Ganti dengan logo Anda jika ada --}}
                    {{-- <img src="{{ public_path('path/to/your/logo.png') }}" alt="logo" width="150"/> --}}
                </td>
            </tr>
        </table>

        <hr>

        <h2 class="document-title">QUOTATION</h2>

        <table class="info-table" style="width: 100%; margin-bottom: 30px;">
            <tr>
                <td style="width: 50%;">
                    <strong>Ditujukan Kepada:</strong><br>
                    {{ $booking->client_name }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>No. Quotation:</strong> {{ $booking->booking_number }}<br>
                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}
                </td>
            </tr>
        </table>
        
        <p>Dengan hormat,<br>
        Terima kasih atas kepercayaan Anda kepada {{ $booking->property->name }}. Berikut kami sampaikan penawaran harga untuk acara Anda:</p>

        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">Deskripsi</th>
                    <th style="width: 30%;">Detail</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jenis Acara</td>
                    <td>{{ $booking->event_type }} @if($booking->miceCategory) ({{ $booking->miceCategory->name }}) @endif</td>
                </tr>
                <tr>
                    <td>Tanggal Acara</td>
                    <td>{{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</td>
                </tr>
                 <tr>
                    <td>Waktu</td>
                    <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                </tr>
                <tr>
                    <td>Jumlah Peserta</td>
                    <td>{{ number_format($booking->participants) }} orang</td>
                </tr>
                <tr>
                    <td>Ruangan / Tempat</td>
                    <td>{{ $booking->room->name ?? 'N/A' }}</td>
                </tr>
                @if($booking->notes)
                <tr>
                    <td>Catatan Tambahan</td>
                    <td>{{ $booking->notes }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="total-section">
            <table>
                <tr>
                    <th style="text-align: left;">Total Harga Penawaran</th>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($booking->total_price, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="notes-terms">
            <strong>Syarat & Ketentuan:</strong>
            <ol>
                <li>Harga di atas sudah termasuk pajak dan layanan.</li>
                <li>Uang muka sebesar 50% (Rp {{ number_format($booking->total_price * 0.5, 2, ',', '.') }}) diperlukan untuk konfirmasi pemesanan.</li>
                <li>Pelunasan dilakukan paling lambat 7 (tujuh) hari sebelum tanggal acara.</li>
                <li>Pembatalan dalam waktu kurang dari 14 hari sebelum acara akan dikenakan biaya pembatalan sebesar 50% dari total harga.</li>
                <li>Rincian lain akan dituangkan dalam kontrak terpisah.</li>
            </ol>
        </div>

        <div class="signature-section">
            <div class="signature" style="float: left;">
                <p>Hormat kami,</p>
                <div class="signature-space"></div>
                <p><strong>{{ $booking->property->name }}</strong><br>(Sales & Marketing)</p>
            </div>
            <div class="signature" style="float: right;">
                <p>Menyetujui,</p>
                <div class="signature-space"></div>
                <p><strong>{{ $booking->client_name }}</strong><br>(Klien)</p>
            </div>
        </div>

    </div>
</body>
</html>