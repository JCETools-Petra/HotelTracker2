<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 Page Not Found</title>
    {{-- Link eksternal dari file asli Anda --}}
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Arvo'>
    
    {{-- Menggunakan asset() helper Laravel untuk memanggil file CSS kita --}}
    <link rel="stylesheet" href="{{ asset('css/404.css') }}">
</head>
<body>

<section class="page_404">
    <div class="container">
        <div class="row">    
            <div class="col-sm-12">
                <div class="col-sm-10 col-sm-offset-1 text-center">
                    <div class="four_zero_four_bg">
                        <h1 class="text-center">404</h1>
                    </div>
                    
                    <div class="contant_box_404">
                        <h3 class="h2">
                            Sepertinya Anda tersesat
                        </h3>
                        
                        <p>Halaman yang Anda cari tidak tersedia!</p>
                        
                        {{-- Menggunakan url() helper Laravel untuk mengarahkan ke halaman utama --}}
                        <a href="{{ url('/') }}" class="link_404">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</body>
</html>