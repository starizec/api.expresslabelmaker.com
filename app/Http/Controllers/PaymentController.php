<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createSession(Request $request, $lang, $licence_uid)
    {
        try {
            Log::info('Creating Stripe session for licence: ' . $licence_uid);
            
            $licence = Licence::where('licence_uid', $licence_uid)
                            ->with(['domain', 'user'])
                            ->first();

            if (!$licence) {
                Log::error('Licence not found: ' . $licence_uid);
                return response()->json(['error' => 'Invalid licence ID'], 404);
            }
            
            Log::info('Licence found: ' . $licence->id);
            
            Stripe::setApiKey(config('services.stripe.secret'));
            Log::info('Stripe API key set');

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Licence for ' . $licence->domain->name,
                        ],
                        'unit_amount' => (int)(100 * 100), // Convert to cents and ensure it's an integer
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('pages.payment', ['lang' => app()->getLocale(), 'licence_uid' => $licence_uid]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pages.payment', ['lang' => app()->getLocale(), 'licence_uid' => $licence_uid]),
            ]);

            Log::info('Stripe session created: ' . $session->id);
            return response()->json(['id' => $session->id]);
        } catch (\Exception $e) {
            Log::error('Error creating Stripe session: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
