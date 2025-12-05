<?php

declare(strict_types=1);

namespace App\Http\Controllers\DeliveryLocations\HR;

use App\Http\Controllers\Controller;
use App\Jobs\FetchHpDeliveryLocations;
use Illuminate\Http\JsonResponse;

class HpController extends Controller
{
    /**
     * Dispatch job to fetch HP delivery locations.
     */
    public function getDeliveryLocations(): JsonResponse
    {
        FetchHpDeliveryLocations::dispatch();

        return response()->json([
            'message' => 'HP delivery locations fetch job has been queued successfully.'
        ]);
    }
}
