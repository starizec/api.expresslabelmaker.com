<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Models\PluginDownload;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function downloadPlugin(string $lang, int $id)
    {
        $pluginDownload = PluginDownload::findOrFail($id);

        // Use the 'public' disk since Filament stores files there
        $disk = Storage::disk('public');
        $filePath = $pluginDownload->plugin_download_link;

        // Check if file exists
        if (!$disk->exists($filePath)) {
            Log::error('Plugin download file not found', [
                'id' => $id,
                'file_path' => $filePath,
                'plugin_download' => $pluginDownload->toArray()
            ]);
            abort(404, 'File not found: ' . $filePath);
        }

        // Get the full path to the file
        $fullPath = $disk->path($filePath);

        // Increment download count
        $pluginDownload->increment('download_count');

        // Get the file name for download
        $fileName = basename($filePath);
        
        // If the stored filename doesn't have extension, try to get it from the path
        if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
            $fileName = 'express-label-maker-' . $pluginDownload->version . '.zip';
        }

        // Return the file download using response()->download()
        return response()->download($fullPath, $fileName, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
