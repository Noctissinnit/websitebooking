<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function get(Request $request){
        return response()->json(User::where('id', $request->id)->first(['name', 'email', 'nis', 'department_id', 'jabatan_id']));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required|email",
            "nis" => "required|numeric",
            "password" => "required",
            "department_id" => 'required|numeric',
            "jabatan_id" => 'required|numeric'
        ]);

        User::create(array_merge(
            $request->all("name", "email", "nis", "department_id", 'jabatan_id'),
            [ 'password' => Hash::make($request->password) ]
        ));

        return redirect()->route("admin.dashboard");
    }

    public function update(Request $request)
    {
        $request->validate([
            "id" => "required|numeric",
            "name" => "required",
            "email" => "required|email",
            "nis" => "required|numeric",
            "department_id" => 'required|numeric',
            "jabatan_id" => 'required|numeric'
        ]);

        User::where("id", $request->id)->update(
            $request->all("name", "email", "nis", "department_id", 'jabatan_id')
        );

        return redirect()->route("admin.dashboard");
    }
    
    public function destroy(User $user){
        $user->delete();
        
        return redirect()->route("admin.dashboard");
    }
    // Tampilkan halaman untuk melakukan booking
    // public function index()
    // {
    //     $rooms = Room::all();  // Ambil semua room yang tersedia
    //     $bookings = Booking::where('user_id', Auth::id())->get(); // Booking yang dibuat oleh user

    //     return view('user.bookings', compact('rooms', 'bookings'));
    // }

    // // Store booking dari user
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'room_id' => 'required|exists:rooms,id',
    //         'start_time' => 'required|date',
    //         'end_time' => 'required|date|after:start_time',
    //         'description' => 'nullable|string',
    //     ]);

    //     Booking::create([
    //         'user_id' => Auth::id(),
    //         'room_id' => $request->room_id,
    //         'start_time' => $request->start_time,
    //         'end_time' => $request->end_time,
    //         'description' => $request->description,
    //         'is_approved' => false, // Booking menunggu persetujuan admin
    //     ]);

    //     return redirect()->back()->with('success', 'Booking berhasil diajukan, menunggu persetujuan admin.');
    // }
}
