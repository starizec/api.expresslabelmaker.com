<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Models\PluginDownload;
use App\Models\Post;
use Carbon\Carbon;

class PageController extends Controller
{
    public function home(string $lang)
    {
        app()->setLocale($lang);

        return view('pages.home');
    }

    public function download(string $lang)
    {
        $post = Post::whereHas('translations', function ($query) {
            $query->where('slug', 'preuzmi-plugin');
        })->with(['translations' => function ($query) use ($lang) {
            $query->where('locale', $lang);
        }])->first();

        $plugin_downloads = PluginDownload::latest()->first();

        return view('pages.download', compact('post', 'plugin_downloads'));
    }

    public function payment(string $lang, string $licence_uid)
    {
        $licence = Licence::where('licence_uid', $licence_uid)->with('domain')->with('user')->latest()->first();
        $valid_until = $licence->valid_until;

        if ($licence->type != config('licence-types.trial')) {
            $valid_until = Carbon::parse($licence->valid_until)->addYear()->addDay()->toDateString();
        }

        $price = 120;

        return view('pages.payment', compact('licence', 'valid_until', 'price'));
    }
}
