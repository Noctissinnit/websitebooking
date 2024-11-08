<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('rooms.index', compact('rooms'));
    }
    
    public function list(){
        return response()->json(Room::with('bookings')->get());
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'required|image',
            'description' => 'required'
        ]);
        $image = $request->image->store('images', ['disk' => 'public']);

        Room::create(array_merge(
            $request->all('name', 'description'),
            [ 'image' => $image ]
        ));
        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image',
            'description' => 'required'
        ]);
        
        $fields = $request->all('name', 'description');
        if($request->has('image')){
            $image = $request->image->store('images', ['disk' => 'public']);
            Storage::disk('public')->delete($room->image ?? '');
            $fields = array_merge($fields, ['image' => $image]);
        }

        $room->update($fields);
        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    // Hapus room (Admin only)
    public function destroy(Room $room)
    {
        Storage::disk('public')->delete($room->image ?? '');
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }
}
