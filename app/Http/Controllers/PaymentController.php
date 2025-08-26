<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function submitOffer(Request $request, $lang)
    {

        $validated = $request->validate([
            'licence_id' => 'required|exists:licences,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'town' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
        ]);

        // Update user information
        $licence = Licence::findOrFail($validated['licence_id']);
        $user = $licence->user;
        
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'company_name' => $validated['company_name'],
            'company_address' => $validated['company_address'],
            'town' => $validated['town'],
            'country' => $validated['country'],
            'vat_number' => $validated['vat_number'],
        ]);

        // Send email with all data to info@expresslabelmaker.com
        try {
            \Mail::send(new \App\Mail\PaymentDataMail($licence));
            Log::info('Payment data email sent successfully for licence: ' . $licence->id);
        } catch (\Exception $e) {
            Log::error('Failed to send payment data email: ' . $e->getMessage());
        }

        // Redirect to payment page with success message
        return redirect()->route('pages.payment', [
            'lang' => $lang,
            'licence_uid' => $licence->licence_uid
        ])->with('success', 'Information updated successfully. Please proceed with payment.');
    }
}
