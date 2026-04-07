<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PricingEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PromoController extends AbstractController
{
    public function __construct(
        private readonly PricingEngine $pricingEngine,
    ) {
    }

    #[Route('/promo/validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $code = $data['code'] ?? null;
        $subtotal = (float) ($data['subtotal'] ?? 0);

        if ($code === null || $code === '') {
            return $this->json(['error' => 'Promo code is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $discounted = $this->pricingEngine->applyPromoCode($subtotal, $code);
        } catch (\InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'Unknown') ? Response::HTTP_NOT_FOUND : Response::HTTP_BAD_REQUEST;
            return $this->json(['error' => $e->getMessage()], $status);
        }

        return $this->json([
            'code' => $code,
            'originalPrice' => $subtotal,
            'discountedPrice' => $discounted,
            'discount' => round($subtotal - $discounted, 2),
        ]);
    }
}
