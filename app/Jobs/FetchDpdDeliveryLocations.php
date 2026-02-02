<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Courier;
use App\Models\DeliveryLocation;
use App\Models\DeliveryLocationHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SFTP;

class FetchDpdDeliveryLocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path = 'app/hr/dpd';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lock = Cache::lock('fetch_dpd_delivery_locations', 600);

        if (!$lock->get()) {
            Log::info('DPD delivery locations fetch already running, skipping');
            return;
        }

        try {
            $courier = Courier::where('name', 'DPD')
                ->whereHas('country', function ($query) {
                    $query->where('short', 'HR');
                })
                ->first();

            if (!$courier) {
                Log::error('DPD courier not found for HR');
                return;
            }

            $sftp = $this->connectToSftp();
            if (!$sftp) {
                Log::error('Failed to connect to DPD SFTP');
                throw new \RuntimeException('DPD SFTP: connection failed or credentials not configured.');
            }

            $files = $sftp->nlist('/OUT/CPF/');
            if ($files === false) {
                Log::error('DPD SFTP nlist failed', ['errors' => $sftp->getErrors()]);
                throw new \RuntimeException('DPD SFTP: failed to list directory /OUT/CPF/');
            }
            $files = array_values(array_filter($files, function ($file) {
                return $file !== '.' && $file !== '..';
            }));

            $storagePath = storage_path($this->path);
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $downloaded = 0;
            foreach ($files as $file) {
                $remotePath = '/OUT/CPF/' . $file;
                $localPath = $storagePath . '/' . $file;
                if ($sftp->get($remotePath, $localPath)) {
                    $downloaded++;
                }
            }
            Log::info('DPD files downloaded', ['count' => $downloaded, 'total_listed' => count($files)]);

            $localFiles = array_diff(scandir($storagePath), ['.', '..']);
            $latestFile = null;
            $latestDate = null;

            foreach ($localFiles as $file) {
                if (preg_match('/D(\d{8})T/', $file, $matches)) {
                    $fileDate = $matches[1];
                    if ($latestDate === null || $fileDate > $latestDate) {
                        $latestDate = $fileDate;
                        $latestFile = $file;
                    }
                }
            }

            if (!$latestFile) {
                Log::error('No valid DPD file found in ' . $storagePath);
                throw new \RuntimeException('DPD: no valid file found (expected filename pattern D{YYYYMMDD}T...).');
            }

            $data = $this->parseDpdFile($latestFile);

            if (empty($data)) {
                Log::warning('DPD file parsed but no HR locations found', ['file' => $latestFile]);
                return;
            }

            $header = DeliveryLocationHeader::create([
                'courier_id' => $courier->id,
                'location_count' => 0,
                'geojson_file_name' => 'U_IZRADI'
            ]);

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
                'location_count' => count($data)
            ]);

            Log::info('DPD delivery locations fetched successfully', [
                'header_id' => $header->id,
                'location_count' => $header->location_count
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching DPD delivery locations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } finally {
            $lock->release();
        }
    }

    /**
     * Connect to SFTP server
     */
    protected function connectToSftp(): ?SFTP
    {
        try {
            $host = env('HR_DPD_SFTP_HOST');
            $username = env('HR_DPD_SFTP_USERNAME');
            $password = env('HR_DPD_SFTP_PASSWORD');

            if (!$host || !$username || !$password) {
                Log::error('DPD SFTP credentials not configured');
                return null;
            }

            $sftp = new SFTP($host, 22);
            $sftp->setTimeout(30);

            if (!$sftp->login($username, $password)) {
                Log::error('SFTP login failed', ['errors' => $sftp->getErrors()]);
                return null;
            }

            return $sftp;
        } catch (\Exception $e) {
            Log::error('SFTP connection error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse DPD file
     */
    protected function parseDpdFile(string $filename): array
    {
        $path = storage_path('app/hr/dpd/' . $filename);

        if (!file_exists($path)) {
            Log::error('DPD file not found', ['file' => $filename]);
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            Log::error('DPD file could not be read', ['file' => $filename]);
            return [];
        }

        $result = [];

        foreach ($lines as $row => $line) {
            $parts = explode(';', $line);
            if (($parts[0] ?? '') !== 'PUDOADDRESS') {
                continue;
            }
            // PUDOADDRESS must be preceded by a PUDO line
            if ($row < 1) {
                continue;
            }

            $pudoInfo = explode(';', $lines[$row - 1]);
            if (($pudoInfo[7] ?? '') !== 'HR') {
                continue;
            }
            if (count($pudoInfo) < 13 || count($parts) < 15) {
                continue;
            }

            $result[] = [
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
        }

        return $result;
    }
}
