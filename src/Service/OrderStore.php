<?php

declare(strict_types=1);

namespace App\Service;

final class OrderStore
{
    /** @var array<string, array<string, mixed>> */
    private array $orders = [];

    private int $nextId = 1;

    /**
     * @param array<string, mixed> $orderData
     * @return array<string, mixed>
     */
    public function save(array $orderData): array
    {
        $id = (string) $this->nextId++;
        $orderData['id'] = $id;
        $this->orders[$id] = $orderData;

        return $orderData;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return $this->orders[$id] ?? null;
    }

    public function clear(): void
    {
        $this->orders = [];
        $this->nextId = 1;
    }
}
