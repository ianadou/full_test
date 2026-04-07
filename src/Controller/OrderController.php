<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\OrderStore;
use App\Service\PricingEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    public function __construct(
        private readonly PricingEngine $pricingEngine,
        private readonly OrderStore $orderStore,
    ) {
    }

    #[Route('/orders/simulate', methods: ['POST'])]
    public function simulate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $result = $this->pricingEngine->calculateOrderTotal(
                $data['items'] ?? [],
                (float) ($data['distance'] ?? 0),
                (float) ($data['weight'] ?? 0),
                $data['promoCode'] ?? null,
                (float) ($data['hour'] ?? 0),
                $data['dayOfWeek'] ?? '',
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'subtotal' => $result->subtotal,
            'discount' => $result->discount,
            'deliveryFee' => $result->deliveryFee,
            'surge' => $result->surge,
            'total' => $result->total,
        ]);
    }

    #[Route('/orders', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $result = $this->pricingEngine->calculateOrderTotal(
                $data['items'] ?? [],
                (float) ($data['distance'] ?? 0),
                (float) ($data['weight'] ?? 0),
                $data['promoCode'] ?? null,
                (float) ($data['hour'] ?? 0),
                $data['dayOfWeek'] ?? '',
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderStore->save([
            'items' => $data['items'],
            'subtotal' => $result->subtotal,
            'discount' => $result->discount,
            'deliveryFee' => $result->deliveryFee,
            'surge' => $result->surge,
            'total' => $result->total,
        ]);

        return $this->json($order, Response::HTTP_CREATED);
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $order = $this->orderStore->find($id);

        if (null === $order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($order);
    }
}
