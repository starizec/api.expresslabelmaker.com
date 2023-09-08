<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courier;
use App\Models\Country;

class CourierController extends Controller
{
    public function index(Courier $couriers, Country $countries)
    {
        return view('admin.couriers.index', [
            'couriers' => $couriers::with('country')->get(),
            'countries' => $countries::get()
        ]);
    }

    public function store(Request $request, Courier $couriers)
    {
        $couriers->create([
            'name' => $request->input('name'),
            'country_id' => $request->input('country_id')
        ]);

        return back();
    }
}
