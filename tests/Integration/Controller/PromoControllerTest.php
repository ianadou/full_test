<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PromoControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function test_validate_returns_200_when_valid_code(): void
    {
        // ARRANGE
        $payload = json_encode(['code' => 'BIENVENUE20', 'subtotal' => 50.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(40.0, (float) $data['discountedPrice']);
        $this->assertSame(10.0, (float) $data['discount']);
    }

    public function test_validate_returns_400_when_expired_code(): void
    {
        // ARRANGE
        $payload = json_encode(['code' => 'EXPIRE', 'subtotal' => 50.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_validate_returns_400_when_below_minimum(): void
    {
        // ARRANGE
        $payload = json_encode(['code' => 'BIENVENUE20', 'subtotal' => 5.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_validate_returns_404_when_unknown_code(): void
    {
        // ARRANGE
        $payload = json_encode(['code' => 'FAKE', 'subtotal' => 50.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_validate_returns_400_when_no_code(): void
    {
        // ARRANGE
        $payload = json_encode(['subtotal' => 50.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }
}
