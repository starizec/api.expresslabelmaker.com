<?php

declare(strict_types=1);

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use App\Jobs\FetchOverseasDeliveryLocations;
use Illuminate\Http\JsonResponse;

class OverseasController extends Controller
{
    /**
     * Dispatch job to fetch Overseas delivery locations.
     */
    public function getDeliveryLocations(): JsonResponse
    {
        FetchOverseasDeliveryLocations::dispatch();

        return response()->json([
            'message' => 'Overseas delivery locations fetch job has been queued successfully.'
        ]);
    }
}
