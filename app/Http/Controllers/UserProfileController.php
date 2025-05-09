<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Licence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $licences = Licence::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return View::make('pages.profile', compact('user', 'licences'));
    }

    /**
     * Update user profile information
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'town' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'vat_number' => 'nullable|string|max:50',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->company_name = $request->company_name;
        $user->company_address = $request->company_address;
        $user->town = $request->town;
        $user->country = $request->country;
        $user->vat_number = $request->vat_number;
        
        $user->save();
        
        return redirect()->route('profile', ['lang' => app()->getLocale()])
            ->with('success', __('messages.profile_updated'));
    }
}