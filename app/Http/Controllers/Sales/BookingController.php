<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\MiceCategory;
use App\Models\PricePackage;
use App\Models\FunctionSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Helper function untuk otorisasi
     */
    private function authorizeAccess(Booking $booking)
    {
        $user = Auth::user();
        // Lewati pengecekan jika user adalah admin atau owner
        if (in_array($user->role, ['admin', 'owner'])) {
            return;
        }

        // Terapkan pengecekan property_id untuk role lain (sales)
        if ($user->property_id != $booking->property_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses booking ini.');
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Booking::query()->with('property', 'miceCategory', 'room');

        // --- Menerapkan semua filter dari request ---

        // Filter pencarian berdasarkan nama klien atau nomor booking
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('client_name', 'like', "%{$searchTerm}%")
                  ->orWhere('booking_number', 'like', "%{$searchTerm}%");
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter berdasarkan rentang tanggal acara
        if ($request->filled('start_date')) {
            $query->whereDate('event_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('event_date', '<=', $request->input('end_date'));
        }

        // --- Logika berdasarkan Role Pengguna ---
        $properties = collect([]);
        if (in_array($user->role, ['admin', 'owner'])) {
            // Admin/Owner bisa memfilter berdasarkan properti
            $properties = Property::orderBy('name')->get();
            if ($request->filled('property_id')) {
                $query->where('property_id', $request->input('property_id'));
            }
        } elseif ($user->role === 'sales') {
            // Sales hanya akan melihat booking dari properti mereka
            $query->where('property_id', $user->property_id);
        } else {
            // Role lain tidak melihat apa-apa
            return view('sales.bookings.index', ['bookings' => collect([]), 'properties' => collect([])]);
        }
        
        // Ambil data setelah semua filter diterapkan dan lakukan pagination
        $bookings = $query->latest()->paginate(15);

        // Kirim data ke view
        return view('sales.bookings.index', compact('bookings', 'properties'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$property = $user->property) {
            return redirect()->route('sales.dashboard')->with('error', 'Akun Anda tidak terikat ke properti manapun.');
        }

        $rooms = $property->rooms()->orderBy('name')->get();
        $miceCategories = MiceCategory::orderBy('name')->get();
        $packages = PricePackage::where('is_active', true)->get();

        return view('sales.bookings.create', compact('property', 'rooms', 'packages', 'miceCategories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->property_id) {
             return redirect()->back()->with('error', 'Akun Anda tidak terikat ke properti manapun.');
        }

        $validatedData = $request->validate([
            'booking_date' => 'required|date',
            'client_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'participants' => 'required|integer|min:1',
            'person_in_charge' => 'required|string|max:255',
            'status' => 'required|in:Booking Sementara,Booking Pasti,Cancel',
            'notes' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'mice_category_id' => 'nullable|exists:mice_categories,id',
            'total_price' => 'required|numeric|min:0',
        ]);

        $bookingData = $validatedData;
        $bookingData['user_id'] = $user->id; // Simpan juga user_id pembuat booking
        $bookingData['event_type'] = $request->input('event_type', 'MICE');
        $bookingData['booking_number'] = 'BKN-' . date('Ymd') . '-' . Str::upper(Str::random(4));
        $bookingData['property_id'] = $user->property_id;
        $bookingData['payment_status'] = 'Pending';

        Booking::create($bookingData);

        return redirect()->route('sales.bookings.index')->with('success', 'Booking baru berhasil ditambahkan.');
    }

    public function show(Booking $booking)
    {
        $this->authorizeAccess($booking);
        return view('sales.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $this->authorizeAccess($booking);

        $property = $booking->property;
        $rooms = $property->rooms()->orderBy('name')->get();
        $miceCategories = MiceCategory::all();
        $packages = PricePackage::where('is_active', true)->get();

        return view('sales.bookings.edit', compact('booking', 'property', 'rooms', 'packages', 'miceCategories'));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorizeAccess($booking);

        $validatedData = $request->validate([
            'booking_date' => 'required|date',
            'client_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'participants' => 'required|integer|min:1',
            'person_in_charge' => 'required|string|max:255',
            'status' => 'required|in:Booking Sementara,Booking Pasti,Cancel',
            'notes' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'mice_category_id' => 'nullable|exists:mice_categories,id',
            'total_price' => 'required|numeric|min:0',
        ]);

        $booking->update($validatedData);

        return redirect()->route('sales.bookings.index')->with('success', 'Booking berhasil diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        $this->authorizeAccess($booking);
        $booking->delete();
        return redirect()->route('sales.bookings.index')->with('success', 'Booking berhasil dihapus.');
    }

    public function beo(Booking $booking)
    {
        $this->authorizeAccess($booking);
        if ($booking->status !== 'Booking Pasti') {
            return redirect()->route('sales.bookings.index')->with('error', 'Aksi tidak diizinkan.');
        }

        $beo = $booking->functionSheet ?? new FunctionSheet();
        $departments = ['SECURITY', 'BANQUET', 'CHEF KICTHEN', 'ENGINEERING', 'PUBLIC AREA', 'STEWARDING', 'FRONT OFFICE', 'ACCOUNTING'];
        $pricePackages = PricePackage::where('is_active', true)->orderBy('name')->get();

        return view('sales.bookings.beo', compact('booking', 'beo', 'departments', 'pricePackages'));
    }

    public function storeBeo(Request $request, Booking $booking)
    {
        if (auth()->user()->property_id != $booking->property_id) {
            abort(403);
        }

        $validated = $request->validate([
            'contact_phone' => 'nullable|string|max:20',
            'room_setup' => 'required|string',
            'price_package_id' => 'required|exists:price_packages,id',
            'event_segments' => 'nullable|array',
            'menu_details' => 'nullable|array',
            'equipment_details' => 'nullable|array',
            'department_notes' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        
        $filterEmpty = function ($items) {
            if (empty($items)) return null;
            return array_filter($items, function ($item) {
                return count(array_filter((array)$item)) > 0;
            });
        };

        $pricePackage = PricePackage::find($validated['price_package_id']);
        
        $booking->functionSheet()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'beo_number' => $booking->functionSheet?->beo_number ?? 'BEO-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'dealed_by' => auth()->user()->name,
                'contact_phone' => $validated['contact_phone'],
                'room_setup' => $validated['room_setup'],
                'price_package_id' => $validated['price_package_id'],
                'notes' => $validated['notes'],
                'event_segments' => $filterEmpty($validated['event_segments'] ?? []),
                'menu_details' => $filterEmpty($validated['menu_details'] ?? []),
                'equipment_details' => $filterEmpty($validated['equipment_details'] ?? []),
                'department_notes' => array_filter($validated['department_notes'] ?? []),
            ]
        );

        $totalPrice = $pricePackage->price * $booking->participants;
        $booking->update(['total_price' => $totalPrice]);

        return redirect()->route('sales.bookings.index')->with('success', 'Function Sheet (BEO) berhasil disimpan.');
    }

    public function printBeo(Booking $booking)
    {
        if (!$booking->functionSheet || auth()->user()->property_id != $booking->property_id) {
            return redirect()->back()->with('error', 'Aksi tidak diizinkan.');
        }

        return view('sales.bookings.print-beo', [
            'booking' => $booking,
            'beo' => $booking->functionSheet
        ]);
    }
    public function showBeo(Booking $booking)
    {
        if (auth()->user()->property_id != $booking->property_id || !$booking->functionSheet) {
            return redirect()->route('sales.bookings.index')->with('error', 'BEO tidak ditemukan atau Anda tidak berwenang.');
        }

        return view('sales.bookings.show-beo', [
            'booking' => $booking,
            'beo' => $booking->functionSheet
        ]);
    }

}
