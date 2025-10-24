<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RemoveCartItemControllerTest extends WebTestCase
{
    private function createCartViaAPI(KernelBrowser $client): string
    {
        $client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}'
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('id', $responseData, 'Response should contain cart id');
        $this->assertIsString($responseData['id'], 'Cart ID should be a string');

        return $responseData['id'];
    }

    private function addItemToCartViaAPI(KernelBrowser $client, string $cartId, string $productId = '550e8400-e29b-41d4-a716-446655440001', string $productName = 'Siroko Cycling Glasses'): string
    {
        $client->request(
            'POST',
            '/api/carts/'.$cartId.'/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => $productId,
                'product_name' => $productName,
                'unit_price' => 99.99,
                'currency' => 'EUR',
                'quantity' => 2,
            ]) ?: '{}'
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);

        // Find the item with the matching product_id
        /** @var array<array{id: string, product_id: string}> $items */
        $items = $responseData['items'];
        foreach ($items as $item) {
            if ($item['product_id'] === $productId) {
                return $item['id'];
            }
        }

        throw new \RuntimeException('Item not found in response');
    }

    public function testItRemovesItemFromCart(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        // Add two items to cart
        $item1Id = $this->addItemToCartViaAPI($client, $cartId, '550e8400-e29b-41d4-a716-446655440001', 'Product 1');
        $item2Id = $this->addItemToCartViaAPI($client, $cartId, '550e8400-e29b-41d4-a716-446655440002', 'Product 2');

        // Remove first item
        $client->request(
            'DELETE',
            '/api/carts/'.$cartId.'/items/'.$item1Id
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');

        $this->assertArrayHasKey('cart_id', $responseData);
        $this->assertEquals($cartId, $responseData['cart_id']);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);
        $this->assertCount(1, $responseData['items']);

        // Verify remaining item is the second one
        /** @var array{id: string, product_id: string} $remainingItem */
        $remainingItem = $responseData['items'][0];
        $this->assertEquals($item2Id, $remainingItem['id']);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440002', $remainingItem['product_id']);
        $this->assertEquals(199.98, $responseData['total']);
    }

    public function testItRemovesLastItemFromCart(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        // Add one item to cart
        $itemId = $this->addItemToCartViaAPI($client, $cartId);

        // Remove the only item
        $client->request(
            'DELETE',
            '/api/carts/'.$cartId.'/items/'.$itemId
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');

        $this->assertArrayHasKey('cart_id', $responseData);
        $this->assertEquals($cartId, $responseData['cart_id']);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);
        $this->assertCount(0, $responseData['items']);
        $this->assertEquals(0, $responseData['total']);
    }

    public function testItReturns404WhenCartNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/api/carts/550e8400-e29b-41d4-a716-446655440000/items/550e8400-e29b-41d4-a716-446655440001'
        );

        $this->assertResponseStatusCodeSame(404);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('Cart not found', $responseData['error']);
    }

    public function testItReturns404WhenCartItemNotFound(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        $client->request(
            'DELETE',
            '/api/carts/'.$cartId.'/items/550e8400-e29b-41d4-a716-446655440001'
        );

        $this->assertResponseStatusCodeSame(404);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('Cart item not found', $responseData['error']);
    }

    public function testItReturns400WhenInvalidCartId(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/api/carts/invalid-uuid/items/550e8400-e29b-41d4-a716-446655440001'
        );

        $this->assertResponseStatusCodeSame(400);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
    }

    public function testItReturns400WhenInvalidCartItemId(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        $client->request(
            'DELETE',
            '/api/carts/'.$cartId.'/items/invalid-uuid'
        );

        $this->assertResponseStatusCodeSame(400);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
    }
}
