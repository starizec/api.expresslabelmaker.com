<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'contactMessage' => 'required|min:10',
        ]);

        Mail::to('info@expresslabelmaker.com')->send(new ContactFormMail($validated));

        return back()->with('success', 'Vaša poruka je uspješno poslana. Odgovorit ćemo vam u najkraćem mogućem roku.');
    }
} 