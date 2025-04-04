<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Licence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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

        return View::make('frontend.profile.index', compact('user', 'licences'));
    }
}