@if (session('success') || session('error') || $errors->any())
    <div class="mb-4">
        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-md" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                <p class="font-bold">Gagal!</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                <p class="font-bold">Terjadi Kesalahan Validasi!</p>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif