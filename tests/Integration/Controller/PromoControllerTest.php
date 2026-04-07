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

    public function testValidateReturns200WhenValidCode(): void
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

    public function testValidateReturns400WhenExpiredCode(): void
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

    public function testValidateReturns400WhenBelowMinimum(): void
    {
        // ARRANGE
        $payload = json_encode(['code' => 'BIENVENUE20', 'subtotal' => 5.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }

    public function testValidateReturns404WhenUnknownCode(): void
    {
        // ARRANGE
        $payload = json_encode(['code' => 'FAKE', 'subtotal' => 50.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(404);
    }

    public function testValidateReturns400WhenNoCode(): void
    {
        // ARRANGE
        $payload = json_encode(['subtotal' => 50.0], JSON_THROW_ON_ERROR);

        // ACT
        $this->client->request('POST', '/promo/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // ASSERT
        $this->assertResponseStatusCodeSame(400);
    }
}
