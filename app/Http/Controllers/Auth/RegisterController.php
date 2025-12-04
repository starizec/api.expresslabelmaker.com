<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Extract the username from email
        $name = explode('@', $request->email)[0];

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send registration confirmation email
        try {
            $user->sendRegistrationConfirmationNotification();
        } catch (\Exception $e) {
            // Log error but don't break registration flow
            Log::error('Error sending registration confirmation email: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect('/' . app()->getLocale() . '/profile')->with('success', __('messages.registration_successful'));
    }
} 