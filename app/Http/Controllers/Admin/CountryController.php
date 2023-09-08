<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    public function index(Country $countries)
    {
        return view('admin.countries.index', [
            "countries" => $countries::all(),
        ]);
    }

    public function store(Request $request, Country $country)
    {
        $country->create([
            'name' => $request->input('name'),
            'short' => $request->input('short')
        ]);

        return back();
    }
}
