<x-sales-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event Calendar') }}
        </h2>
    </x-slot>

    {{-- Sisipkan style untuk FullCalendar --}}
    @push('styles')
    <style>
        /* Pastikan kalender memiliki tinggi */
        #calendar {
            min-height: 70vh;
        }
        /* Style kustom untuk event di kalender */
        .fc-event {
            cursor: pointer;
            border: none !important;
        }
        .fc-event .fc-event-main {
            padding: 5px;
            font-size: 0.8rem;
        }
    </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Wadah untuk Kalender --}}
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sisipkan script untuk FullCalendar --}}
    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Tampilan awal: bulanan
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek' // Pilihan view
                },
                // Ambil data event dari route yang kita buat di controller
                events: '{{ route("sales.calendar.events") }}',
                // Buat event bisa diklik
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // Mencegah browser mengikuti link
                    if (info.event.url) {
                        window.location.href = info.event.url; // Arahkan ke link secara manual
                    }
                },
                eventDisplay: 'block', // Tampilkan event sebagai blok
                dayMaxEvents: true, // Batasi jumlah event per hari dengan tombol "+ more"
            });
            calendar.render();
        });
    </script>
    @endpush
</x-sales-layout>
