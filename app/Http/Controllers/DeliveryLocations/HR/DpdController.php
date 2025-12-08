<?php

declare(strict_types=1);

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use App\Jobs\FetchDpdDeliveryLocations;
use Illuminate\Http\JsonResponse;

class DpdController extends Controller
{
    /**
     * Dispatch job to fetch DPD delivery locations.
     */
    public function getDeliveryLocations(): JsonResponse
    {
        FetchDpdDeliveryLocations::dispatch();

        return response()->json([
            'message' => 'DPD delivery locations fetch job has been queued successfully.'
        ]);
    }
}
