<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GetCartControllerTest extends WebTestCase
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

    private function addItemToCartViaAPI(KernelBrowser $client, string $cartId, string $productId = '550e8400-e29b-41d4-a716-446655440001', string $productName = 'Siroko Cycling Glasses', float $unitPrice = 99.99, int $quantity = 2): void
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
                'unit_price' => $unitPrice,
                'currency' => 'EUR',
                'quantity' => $quantity,
            ]) ?: '{}'
        );

        $this->assertResponseIsSuccessful();
    }

    public function testItReturnsCartWithItems(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        // Add items to cart
        $this->addItemToCartViaAPI($client, $cartId, '550e8400-e29b-41d4-a716-446655440001', 'Premium Sunglasses', 129.99, 1);
        $this->addItemToCartViaAPI($client, $cartId, '550e8400-e29b-41d4-a716-446655440002', 'Sports Helmet', 89.99, 2);

        // Get cart
        $client->request('GET', '/api/carts/'.$cartId);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');

        // Verify cart structure
        $this->assertArrayHasKey('cart_id', $responseData);
        $this->assertEquals($cartId, $responseData['cart_id']);
        $this->assertArrayHasKey('total', $responseData);
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);

        // Verify items
        $this->assertCount(2, $responseData['items']);

        // Verify total calculation: 129.99 + (89.99 * 2) = 309.97
        $this->assertEquals(309.97, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);

        // Verify item details
        $itemFound = false;
        /** @var array<array{id: string, product_id: string, product_name: string, unit_price: float, currency: string, quantity: int, subtotal: float}> $items */
        $items = $responseData['items'];
        foreach ($items as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('product_id', $item);
            $this->assertArrayHasKey('product_name', $item);
            $this->assertArrayHasKey('unit_price', $item);
            $this->assertArrayHasKey('currency', $item);
            $this->assertArrayHasKey('quantity', $item);
            $this->assertArrayHasKey('subtotal', $item);

            if ('550e8400-e29b-41d4-a716-446655440001' === $item['product_id']) {
                $itemFound = true;
                $this->assertEquals('Premium Sunglasses', $item['product_name']);
                $this->assertEquals(129.99, $item['unit_price']);
                $this->assertEquals(1, $item['quantity']);
                $this->assertEquals(129.99, $item['subtotal']);
            }
        }

        $this->assertTrue($itemFound, 'Premium Sunglasses item should be found in response');
    }

    public function testItReturnsEmptyCart(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        // Get empty cart
        $client->request('GET', '/api/carts/'.$cartId);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');

        // Verify empty cart structure
        $this->assertArrayHasKey('cart_id', $responseData);
        $this->assertEquals($cartId, $responseData['cart_id']);
        $this->assertArrayHasKey('total', $responseData);
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('items', $responseData);

        // Verify empty cart values
        $this->assertEquals(0, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertIsArray($responseData['items']);
        $this->assertCount(0, $responseData['items']);
    }

    public function testItReturns404WhenCartNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/carts/550e8400-e29b-41d4-a716-446655440000');

        $this->assertResponseStatusCodeSame(404);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('Cart not found', $responseData['error']);
    }

    public function testItReturns400WhenInvalidCartId(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/carts/invalid-uuid');

        $this->assertResponseStatusCodeSame(400);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
    }

    public function testItReturnsCartWithUpdatedTotalAfterItemChanges(): void
    {
        $client = static::createClient();

        // Create cart and add item
        $cartId = $this->createCartViaAPI($client);
        $this->addItemToCartViaAPI($client, $cartId, '550e8400-e29b-41d4-a716-446655440001', 'Test Product', 50.00, 2);

        // Get cart and verify initial total
        $client->request('GET', '/api/carts/'.$cartId);
        $this->assertResponseIsSuccessful();

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent);
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData);
        $this->assertEquals(100.00, $responseData['total']); // 50.00 * 2

        // Add another different item
        $this->addItemToCartViaAPI($client, $cartId, '550e8400-e29b-41d4-a716-446655440002', 'Another Product', 25.00, 1);

        // Get cart again and verify updated total
        $client->request('GET', '/api/carts/'.$cartId);
        $this->assertResponseIsSuccessful();

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent);
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData);
        $this->assertIsArray($responseData['items']);

        $this->assertCount(2, $responseData['items']);
        $this->assertEquals(125.00, $responseData['total']); // 100.00 + 25.00
    }
}
