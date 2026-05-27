<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    public function createCheckout(array $payload, string $idempotencyKey): array;

    public function getCheckoutDetail(string $checkoutId): array;

    public function checkCheckoutStatus(string $checkoutId): array;

    public function cancelCheckout(string $checkoutId): array;
}
