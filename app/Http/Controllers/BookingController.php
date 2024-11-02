<?php

namespace App\Http\Controllers;

use App\Mail\BookingApprovedMail;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Mail\InvitationMail;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function list(Request $request)
    {
        $bookings = Booking::with('room')->with('department')->with('user:id,nis,name');
        if ($request->date) $bookings = $bookings->whereDate('date', $request->date);
        if ($request->room_id) $bookings = $bookings->where('room_id', $request->room_id);

        $bookings = $bookings->get();

        return response()->json($bookings);
    }
    // Hanya menampilkan form booking untuk user biasa
    public function create(Request $request, int $id)
    {
        $roomId = $id;
        $room = Room::where('id', $roomId)->first();
        $users = User::all();

        $user_department = null;
        if(session('google_bookings_user_id') && session('google_bookings_date') && session('google_access_token')){
            $user_department = User::find(session('google_bookings_user_id'))->department;
        }
        $officeMode = false;
        if($request->has('office')){
            $officeMode = true;
        }

        return view("bookings.create", compact("room", "roomId", "users", 'user_department', 'officeMode'));
    }

    public function login(Request $request)
    {
        $request->validate([
            "nis" => ["required"],
            "password" => ["required"],
        ]);
        $user = User::where("nis", $request->nis)->with('department')->first();
        $success = $user !== null && Hash::check($request->password, $user->password);

        return response()->json([
            "success" => $success,
            "data" => $success ? $user : null
        ]);
    }

    // Menyimpan booking baru
    public function store(Request $request)
    {
        $request->validate([
            "room_id" => "required",
            "date" => "required|date",
            "start_time" => "required",
            "end_time" => "required",
            "description" => "required",
            "department_id" => "required|numeric",
            "users" => "nullable",
            "date" => "nullable",
        ]);

        $user = User::find(session('google_bookings_user_id'));
        $booking = Booking::create(array_merge($request->all('room_id', 'date', 'start_time', 'end_time', 'description', 'department_id'), [
            "user_id" => $user->id,
            // 'approved' => false, // Menunggu approval
            "approved" => true, // Otomatis approve
        ]));
        if ($request->users) $booking->users()->sync($request->users);
        $users = Booking::where('id', $booking->id)->first()->users;

        foreach ($users as $user) {
            Mail::to($user)->send(new InvitationMail($booking, $user));
        }

        $accessToken = session('google_access_token');
        if (config('services.google.calendar_enable') && !$accessToken) {
            return response()->json(['error' => 'No access token found. Please authenticate.'], 401);
        }

        // Remove all sessions
        $request->session()->remove('google_access_token');
        $request->session()->remove('google_bookings_date');
        $request->session()->remove('google_bookings_room_id');
        

        // Create calendar
        if(config('services.google.calendar_enable')){
            $client = new GoogleClient();
            $client->setAccessToken($accessToken);

            $calendarService = new GoogleCalendar($client);
            $attendees = [];
            $attendees[] = ['email' => $booking->user->email];
            foreach ($booking->users as $user) {
                $attendees[] = ['email' => $user->email];
            }

            $event = new Event([
                'summary' => 'Booking Invitation',
                'description' => $booking->description,
                'start' => new EventDateTime([
                    'dateTime' => Carbon::parse($booking->date . ' ' . $booking->start_time),
                    'timeZone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => Carbon::parse($booking->date . ' ' . $booking->end_time),
                    'timeZone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
                ]),
                'attendees' => $attendees,
            ]);

            try {
                $calendarService->events->insert('primary', $event);
                return response()->json(['success' => 'Event created successfully.']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to create event: ' . $e->getMessage()], 500);
            }
        }

        return redirect()
            ->route("bookings.create", $request->room_id)
            ->with("success", "Booking berhasil ditambahkan.");
    }

    public function destroy(Request $request)
    {
        if (Auth::user()->role !== "admin") {
            return redirect()
                ->route("home")
                ->with("error", "Unauthorized access");
        }

        $booking = Booking::where('id', $request->id);
        $roomId = $booking->first()->room_id;
        $booking->delete();

        return redirect()
            ->route("bookings.create", $roomId)
            ->with("success", "Booking berhasil dihapus.");
    }

    // Menampilkan booking untuk admin
    public function indexAdmin()
    {
        if (Auth::user()->role !== "admin") {
            return redirect()
                ->route("home")
                ->with("error", "Unauthorized access");
        }

        $bookings = Booking::all();
        return view("admin.bookings.index", compact("bookings"));
    }

    // Proses approve booking oleh admin
    public function approve($id)
    {
        if (Auth::user()->role !== "admin") {
            return redirect()
                ->route("home")
                ->with("error", "Unauthorized access");
        }

        $booking = Booking::find($id);
        if ($booking) {
            $booking->approved = true;
            $booking->save();

            // Kurangi 7 jam dari waktu mulai dan selesai
            $startDateTime = Carbon::parse(
                $booking->date . " " . $booking->start_time
            )->subHours(7);
            $endDateTime = Carbon::parse(
                $booking->date . " " . $booking->end_time
            )->subHours(7);

            // Buat event di sistem Anda (misal dengan Spatie Google Calendar)
            // $event = new Event;
            // $event->name = 'Meeting Room Booking: ' . $booking->room->name;
            // $event->startDateTime = $startDateTime;
            // $event->endDateTime = $endDateTime;
            // $event->description = $booking->description;
            // $event->save();

            // Kirim email notifikasi setelah approve
            Mail::to($booking->user->email)->send(
                new BookingApprovedMail($booking)
            );

            // Ambil token OAuth dari session
            $accessToken = session("google_access_token");

            // Jika token tidak ada, arahkan pengguna untuk login ulang dengan Google
            if (!$accessToken) {
                return redirect()
                    ->route("login.google")
                    ->with(
                        "error",
                        "Please login with Google to sync your calendar."
                    );
            }

            // Inisialisasi Google Client dengan token
            $client = new \Google_Client();
            $client->setAccessToken($accessToken);

            $service = new \Google_Service_Calendar($client);

            // Membuat event untuk disimpan di kalender pengguna
            $googleEvent = new \Google_Service_Calendar_Event([
                "summary" => "Meeting Room Booking: " . $booking->room->name,
                "start" => [
                    "dateTime" => $startDateTime->toRfc3339String(), // Gunakan waktu yang sudah dikurangi 7 jam
                    "timeZone" => "Asia/Jakarta",
                ],
                "end" => [
                    "dateTime" => $endDateTime->toRfc3339String(), // Gunakan waktu yang sudah dikurangi 7 jam
                    "timeZone" => "Asia/Jakarta",
                ],
                "attendees" => [
                    ["email" => $booking->user->email], // email pengguna yang diundang
                ],
            ]);

            // Simpan event ke kalender utama pengguna
            $service->events->insert("primary", $googleEvent);

            return redirect()
                ->route("admin.bookings.index")
                ->with(
                    "success",
                    "Booking approved and event created in Google Calendar."
                );
        }

        return redirect()
            ->route("admin.bookings.index")
            ->with("error", "Booking not found.");
    }
}
