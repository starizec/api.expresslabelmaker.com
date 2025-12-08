<?php

declare(strict_types=1);

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use App\Jobs\FetchGlsDeliveryLocations;
use Illuminate\Http\JsonResponse;

class GlsController extends Controller
{
    /**
     * Dispatch job to fetch GLS delivery locations.
     */
    public function getDeliveryLocations(): JsonResponse
    {
        FetchGlsDeliveryLocations::dispatch();

        return response()->json([
            'message' => 'GLS delivery locations fetch job has been queued successfully.'
        ]);
    }
}
