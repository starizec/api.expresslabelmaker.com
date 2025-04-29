<?php

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use phpseclib3\Net\SFTP;

use App\Models\Courier;
use App\Models\DeliveryLocationHeader;
use App\Models\DeliveryLocation;
class DpdController extends Controller
{
    protected $courier;
    protected $sftp;
    protected $host;
    protected $username;
    protected $password;

    protected $path = 'app/hr/dpd';

    public function __construct()
    {
        $this->host = env('HR_DPD_SFTP_HOST');
        $this->username = env('HR_DPD_SFTP_USERNAME');
        $this->password = env('HR_DPD_SFTP_PASSWORD');

        $this->courier = Courier::where('name', 'DPD')
            ->whereHas('country', function ($query) {
                $query->where('short', 'HR');
            })
            ->first();

        $this->connectToSftp();
    }

    public function getDeliveryLocations()
    {
        $header = DeliveryLocationHeader::create([
            'courier_id' => $this->courier->id,
            'location_count' => 0,
            'geojson_file_name' => 'U_IZRADI'
        ]);

         $files = $this->sftp->nlist('/OUT/CPF/');
         $downloadedFiles = [];

         $files = array_filter($files, function ($file) {
             return $file !== '.' && $file !== '..';
         });

         $storagePath = storage_path('app/hr/dpd');
         if (!file_exists($storagePath)) {
             mkdir($storagePath, 0755, true);
         }

         foreach ($files as $file) {
             $remotePath = '/OUT/CPF/' . $file;
             $localPath = $storagePath . '/' . $file;

             if ($this->sftp->get($remotePath, $localPath)) {
                 $downloadedFiles[] = $file;
             }
         }

        $localPath = storage_path($this->path);

        $files = array_diff(scandir($localPath), ['.', '..']);

        $latestFile = null;
        $latestDate = null;

        foreach ($files as $file) {
            if (preg_match('/D(\d{8})T/', $file, $matches)) {
                $fileDate = $matches[1];
                if ($latestDate === null || $fileDate > $latestDate) {
                    $latestDate = $fileDate;
                    $latestFile = $file;
                }
            }
        }

        $data = $this->parseDpdFile($latestFile);

        foreach ($data as $item) {
            DeliveryLocation::create([
                'header_id' => $header->id,
                'location_id' => $item['location_id'],
                'place' => $item['place'],
                'postal_code' => $item['postal_code'],
                'street' => $item['street'],
                'house_number' => $item['house_number'] ?? null,
                'lon' => $item['lon'],
                'lat' => $item['lat'],
                'name' => $item['name'],
                'type' => $item['type'],
                'description' => null,
                'phone' => $item['phone'] ?? null,
                'active' => $item['active'],
            ]);
        }

        $header->update([
            'location_count' => DeliveryLocation::where('header_id', $header->id)->count()
        ]);
    }

    public function connectToSftp()
    {
        try {
            $this->sftp = new SFTP($this->host, 22);
            $this->sftp->setTimeout(30);

            if (!$this->sftp->login($this->username, $this->password)) {
                throw new \Exception('SFTP login failed: ' . json_encode($this->sftp->getErrors()));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function parseDpdFile($filename)
    {
        $path = storage_path('app/hr/dpd/' . $filename);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $result = [];

        foreach ($lines as $row => $line) {
            $parts = explode(';', $line);

            if ($parts[0] === 'PUDOADDRESS') {
                $pudoInfo = $lines[$row - 1];
                $pudoInfo = explode(';', $pudoInfo);

                if ($pudoInfo[7] !== 'HR') {
                    continue;
                }

                $item = [
                    'location_id' => $pudoInfo[2],
                    'place' => $parts[15] ?? '',
                    'postal_code' => $parts[13] ?? '',
                    'street' => $parts[3] ?? '',
                    'house_number' => $parts[4] ?? null,
                    'lon' => isset($parts[18]) ? (float) $parts[18] : null,
                    'lat' => isset($parts[17]) ? (float) $parts[17] : null,
                    'name' => $pudoInfo[5],
                    'type' => $pudoInfo[12],
                    'description' => null,
                    'phone' => $parts[7] ?? null,
                    'active' => true,
                ];

                $result[] = $item;
            }
        }

        return $result;
    }
}
