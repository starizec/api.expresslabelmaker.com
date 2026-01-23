<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluginDownload extends Model
{
    protected $table = 'plugin_download';

    protected $fillable = [
        'version',
        'plugin_download_link',
        'download_count'
    ];
}
