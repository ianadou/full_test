<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Service\OrderStore;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();

        /** @var OrderStore $store */
        $store = static::getContainer()->get(OrderStore::class);
        $store->clear();
    }

    private function validPayload(): string
    {
        return json_encode([
            'items' => [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 2]],
            'distance' => 5,
            'weight' => 2,
            'hour' => 15,
            'dayOfWeek' => 'tuesday',
        ], JSON_THROW_ON_ERROR);
    }

    // --- POST /orders/simulate ---

    public function test_simulate_returns_200_when_valid_order(): void
    {
        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());

        // ASSERT
        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(25.0, (float) $data['subtotal']);
        $this->assertSame(3.0, (float) $data['deliveryFee']);
        $this->assertSame(28.0, (float) $data['total']);
    }

    public function test_simulate_applies_promo_code(): void
    {
        // ARRANGE
        $payload = json_encode([
            'items' => [['name' => 'Sushi', 'price' => 25.0, 'quantity' => 2]],
            'distance' => 2,
            'weight' => 1,
            'promoCode' => 'BIENVENUE20',
            'hour' => 15,
            'dayOfWeek' => 'tuesday',
        ], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(10.0, (float) $data['discount']);
    }

    public function test_simulate_returns_400_when_expired_promo(): void
    {
        // ARRANGE
        $payload = json_encode([
            'items' => [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 2]],
            'distance' => 2,
            'weight' => 1,
            'promoCode' => 'EXPIRE',
            'hour' => 15,
            'dayOfWeek' => 'tuesday',
        ], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_simulate_returns_400_when_empty_cart(): void
    {
        // ARRANGE
        $payload = json_encode([
            'items' => [],
            'distance' => 2,
            'weight' => 1,
            'hour' => 15,
            'dayOfWeek' => 'tuesday',
        ], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_simulate_returns_400_when_beyond_delivery_zone(): void
    {
        // ARRANGE
        $payload = json_encode([
            'items' => [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 1]],
            'distance' => 15,
            'weight' => 1,
            'hour' => 15,
            'dayOfWeek' => 'tuesday',
        ], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_simulate_returns_400_when_restaurant_closed(): void
    {
        // ARRANGE
        $payload = json_encode([
            'items' => [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 1]],
            'distance' => 2,
            'weight' => 1,
            'hour' => 23,
            'dayOfWeek' => 'tuesday',
        ], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_simulate_applies_surge_to_delivery(): void
    {
        // ARRANGE — vendredi 20h → surge 1.8, delivery 5km = 3€ * 1.8 = 5.4€
        $payload = json_encode([
            'items' => [['name' => 'Burger', 'price' => 15.0, 'quantity' => 1]],
            'distance' => 5,
            'weight' => 1,
            'hour' => 20,
            'dayOfWeek' => 'friday',
        ], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders/simulate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(5.4, (float) $data['deliveryFee']);
    }

    // --- POST /orders ---

    public function test_store_returns_201_with_id(): void
    {
        // ACT
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());

        // ASSERT
        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
    }

    public function test_store_order_is_retrievable(): void
    {
        // ARRANGE
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // ACT
        $this->client->request('GET', '/orders/' . $data['id']);

        // ASSERT
        $this->assertResponseStatusCodeSame(200);
        $order = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($data['id'], $order['id']);
    }

    public function test_store_creates_unique_ids(): void
    {
        // ACT
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());
        $first = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());
        $second = json_decode($this->client->getResponse()->getContent(), true);

        // ASSERT
        $this->assertNotSame($first['id'], $second['id']);
    }

    public function test_store_returns_400_when_invalid(): void
    {
        // ARRANGE
        $payload = json_encode(['items' => [], 'distance' => 2, 'weight' => 1, 'hour' => 15, 'dayOfWeek' => 'tuesday'], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_store_does_not_persist_invalid_order(): void
    {
        // ARRANGE
        $payload = json_encode(['items' => [], 'distance' => 2, 'weight' => 1, 'hour' => 15, 'dayOfWeek' => 'tuesday'], JSON_THROW_ON_ERROR);
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ACT
        $this->client->request('GET', '/orders/1');

        // ASSERT
        $this->assertResponseStatusCodeSame(404);
    }

    // --- GET /orders/:id ---

    public function test_show_returns_200_when_order_exists(): void
    {
        // ARRANGE
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // ACT
        $this->client->request('GET', '/orders/' . $data['id']);

        // ASSERT
        $this->assertResponseStatusCodeSame(200);
        $order = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('subtotal', $order);
        $this->assertArrayHasKey('total', $order);
    }

    public function test_show_returns_404_when_order_not_found(): void
    {
        // ACT
        $this->client->request('GET', '/orders/999');

        // ASSERT
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_show_returns_correct_structure(): void
    {
        // ARRANGE
        $this->client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $this->validPayload());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        // ACT
        $this->client->request('GET', '/orders/' . $data['id']);
        $order = json_decode($this->client->getResponse()->getContent(), true);

        // ASSERT
        $this->assertArrayHasKey('id', $order);
        $this->assertArrayHasKey('items', $order);
        $this->assertArrayHasKey('subtotal', $order);
        $this->assertArrayHasKey('deliveryFee', $order);
        $this->assertArrayHasKey('total', $order);
    }
}
