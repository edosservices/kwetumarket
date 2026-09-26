<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class WebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, PaymentService $payments): Response
    {
        abort_unless(in_array($provider, ['mpesa', 'airtel', 'orange', 'afrimoney', 'card', 'sandbox'], true), 404);

        try {
            $payments->acceptWebhook($provider, $request->getContent(), $request->header('X-Twende-Signature'));
        } catch (ValidationException $exception) {
            return response($exception->getMessage(), 422);
        }

        return response()->noContent();
    }
}
