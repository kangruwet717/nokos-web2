<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentGatewayManager
{
    public function gateway(string $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            'dompetx' => app(PaymentGatewayInterface::class),
            'jagopay' => app(JagopayClient::class),
            default => throw new InvalidArgumentException("Unsupported payment provider [{$provider}]."),
        };
    }

    public function providerForMethod(string $method): string
    {
        return match ($method) {
            'qris1' => 'dompetx',
            'qris2' => 'jagopay',
            default => throw new InvalidArgumentException("Unsupported payment method [{$method}]."),
        };
    }

    public function methodLabel(string $method): string
    {
        return match ($method) {
            'qris1' => 'QRIS 1',
            'qris2' => 'QRIS 2',
            default => strtoupper($method),
        };
    }
}
