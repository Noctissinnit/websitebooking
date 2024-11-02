<?php
namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(Request $request)
    {
        if($request->has('bookings_date')){
            $request->session()->put('google_bookings_date', $request->bookings_date);
        }
        if($request->has('bookings_room_id')){
            $request->session()->put('google_bookings_room_id', $request->bookings_room_id);
        }
        $request->session()->save();
        return Socialite::driver('google')->with([
            'approval_prompt' => config('services.google.approval_prompt'),
            'access_type' => config('services.google.access_type'),
            'include_granted_scopes' => config('services.google.include_granted_scopes'),
        ])->scopes(['https://www.googleapis.com/auth/calendar'])->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail());
            if(!$user->exists()){
                return redirect()->route(session('google_bookings_date') ? 'bookings.create' : 'home',
                    ['id' => session('google_bookings_room_id')])
                    ->with('error', 'Email user tidak dapat ditemukan di database!');
            }

            if(session('google_bookings_date') && session('google_bookings_room_id')){
                $request->session()->put('google_access_token', $googleUser->token);
                $request->session()->put('google_bookings_user_id', $user->first()->id);
                $request->session()->save();
                return redirect()->route('bookings.create', [
                    'id' => session('google_bookings_room_id')
                ]);
            }

            return redirect()->route('home')->with('error', 'Terjadi kesalahan pada booking! Silahkan buat ulang.'); 
        } catch (\Exception $e) {
            Log::debug('Error: ', [$e]);
            return redirect()->route('home')->with('error', 'Failed to login with Google: ' . $e->getMessage());
        }

    } 
}
