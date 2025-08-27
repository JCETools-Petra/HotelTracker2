<h1>Buat Reservasi Baru</h1>

@if(session('success'))
    <div style="color: green;">{{ session('success') }}</div>
@endif

<form action="{{ route('ecommerce.reservations.store') }}" method="POST">
    @csrf
    <div>
        <label>Properti</label>
        <select name="property_id" id="property_id" required>
            <option value="">-- Pilih Properti --</option>
            @foreach($properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Tanggal Check-in</label>
        <input type="date" name="checkin_date" id="checkin_date" required>
    </div>
    <hr>
    <h3>Harga Saat Ini: Rp <span id="current_price">...</span></h3>
    <hr>
    <div>
        <label>Sumber OTA</label>
        <select name="source" required>
            @foreach($sources as $source)
                <option value="{{ $source }}">{{ $source }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Nama Tamu</label>
        <input type="text" name="guest_name" required>
    </div>
    <div>
        <label>Tanggal Check-out</label>
        <input type="date" name="checkout_date" required>
    </div>
    <button type="submit">Simpan Reservasi</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const propertySelect = document.getElementById('property_id');
        const dateInput = document.getElementById('checkin_date');
        const priceDisplay = document.getElementById('current_price');

        function fetchPrice() {
            const propertyId = propertySelect.value;
            const checkinDate = dateInput.value;

            if (!propertyId || !checkinDate) return;

            priceDisplay.textContent = 'Menghitung...';
            const url = `{{ route('ecommerce.reservations.getPrice') }}?property_id=${propertyId}&checkin_date=${checkinDate}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    priceDisplay.textContent = data.price || '0';
                })
                .catch(() => {
                    priceDisplay.textContent = 'Error';
                });
        }

        propertySelect.addEventListener('change', fetchPrice);
        dateInput.addEventListener('change', fetchPrice);
    });
</script>