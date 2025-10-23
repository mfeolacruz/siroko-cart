<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UpdateCartItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testItUpdatesCartItemQuantity(): void
    {
        $cartId = $this->createCartViaAPI();
        $itemId = $this->addItemToCartViaAPI($cartId);

        $this->client->request(
            'PUT',
            "/api/carts/{$cartId}/items/{$itemId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"quantity": 5}'
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);
        $this->assertCount(1, $responseData['items']);
        $this->assertIsArray($responseData['items'][0]);
        $this->assertEquals(5, $responseData['items'][0]['quantity']);
    }

    public function testItReturnsNotFoundWhenCartDoesNotExist(): void
    {
        $nonExistentCartId = '550e8400-e29b-41d4-a716-446655440000';
        $nonExistentItemId = '550e8400-e29b-41d4-a716-446655440001';

        $this->client->request(
            'PUT',
            "/api/carts/{$nonExistentCartId}/items/{$nonExistentItemId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"quantity": 3}'
        );

        $this->assertResponseStatusCodeSame(404);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('Cart with id', $responseData['error']);
    }

    public function testItReturnsNotFoundWhenCartItemDoesNotExist(): void
    {
        $cartId = $this->createCartViaAPI();
        $nonExistentItemId = '550e8400-e29b-41d4-a716-446655440001';

        $this->client->request(
            'PUT',
            "/api/carts/{$cartId}/items/{$nonExistentItemId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"quantity": 3}'
        );

        $this->assertResponseStatusCodeSame(404);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('Cart item with id', $responseData['error']);
    }

    public function testItReturnsBadRequestForInvalidQuantity(): void
    {
        $cartId = $this->createCartViaAPI();
        $itemId = $this->addItemToCartViaAPI($cartId);

        $this->client->request(
            'PUT',
            "/api/carts/{$cartId}/items/{$itemId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"quantity": 0}'
        );

        $this->assertResponseStatusCodeSame(400);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testItReturnsBadRequestForMissingQuantity(): void
    {
        $cartId = $this->createCartViaAPI();
        $itemId = $this->addItemToCartViaAPI($cartId);

        $this->client->request(
            'PUT',
            "/api/carts/{$cartId}/items/{$itemId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}'
        );

        $this->assertResponseStatusCodeSame(400);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testItReturnsBadRequestForInvalidJSON(): void
    {
        $cartId = $this->createCartViaAPI();
        $itemId = $this->addItemToCartViaAPI($cartId);

        $this->client->request(
            'PUT',
            "/api/carts/{$cartId}/items/{$itemId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"quantity": invalid}'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    private function createCartViaAPI(): string
    {
        $this->client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}'
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('id', $responseData, 'Response should contain cart id');
        $this->assertIsString($responseData['id'], 'Cart ID should be a string');

        return $responseData['id'];
    }

    private function addItemToCartViaAPI(string $cartId): string
    {
        $this->client->request(
            'POST',
            "/api/carts/{$cartId}/items",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'product_id' => '550e8400-e29b-41d4-a716-446655440001',
                'product_name' => 'Test Product',
                'unit_price' => 2999,
                'currency' => 'EUR',
                'quantity' => 2,
            ])
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);
        $this->assertCount(1, $responseData['items']);
        $this->assertIsArray($responseData['items'][0]);
        $this->assertArrayHasKey('id', $responseData['items'][0]);
        $this->assertIsString($responseData['items'][0]['id']);

        return $responseData['items'][0]['id'];
    }
}
