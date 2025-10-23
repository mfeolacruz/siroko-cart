<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AddCartItemControllerTest extends WebTestCase
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

    public function testItAddsItemToCart(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        // Add item to cart
        $client->request(
            'POST',
            '/api/carts/'.$cartId.'/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => '550e8400-e29b-41d4-a716-446655440001',
                'product_name' => 'Siroko Cycling Glasses',
                'unit_price' => 99.99,
                'currency' => 'EUR',
                'quantity' => 2,
            ]) ?: '{}'
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
        $this->assertIsArray($responseData['items'][0]);
        $this->assertEquals('Siroko Cycling Glasses', $responseData['items'][0]['product_name']);
        $this->assertEquals(99.99, $responseData['items'][0]['unit_price']);
        $this->assertEquals(2, $responseData['items'][0]['quantity']);
        $this->assertEquals(199.98, $responseData['items'][0]['subtotal']);
        $this->assertEquals(199.98, $responseData['total']);
    }

    public function testItReturns404WhenCartNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/carts/550e8400-e29b-41d4-a716-446655440000/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => '550e8400-e29b-41d4-a716-446655440001',
                'product_name' => 'Siroko Cycling Glasses',
                'unit_price' => 99.99,
                'currency' => 'EUR',
                'quantity' => 2,
            ]) ?: '{}'
        );

        $this->assertResponseStatusCodeSame(404);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('not found', $responseData['error']);
    }

    public function testItReturns400WhenInvalidQuantity(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        $client->request(
            'POST',
            '/api/carts/'.$cartId.'/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => '550e8400-e29b-41d4-a716-446655440001',
                'product_name' => 'Siroko Cycling Glasses',
                'unit_price' => 99.99,
                'currency' => 'EUR',
                'quantity' => 0,
            ]) ?: '{}'
        );

        $this->assertResponseStatusCodeSame(400);

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertStringContainsString('positive integer', $responseData['error']);
    }

    public function testItIncreasesQuantityWhenAddingSameProduct(): void
    {
        $client = static::createClient();

        // Create cart via API
        $cartId = $this->createCartViaAPI($client);

        $productId = '550e8400-e29b-41d4-a716-446655440001';

        // Add item first time
        $client->request(
            'POST',
            '/api/carts/'.$cartId.'/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => $productId,
                'product_name' => 'Siroko Cycling Glasses',
                'unit_price' => 99.99,
                'currency' => 'EUR',
                'quantity' => 2,
            ]) ?: '{}'
        );

        $this->assertResponseIsSuccessful();

        // Add same item again
        $client->request(
            'POST',
            '/api/carts/'.$cartId.'/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => $productId,
                'product_name' => 'Siroko Cycling Glasses',
                'unit_price' => 99.99,
                'currency' => 'EUR',
                'quantity' => 3,
            ]) ?: '{}'
        );

        $this->assertResponseIsSuccessful();

        $responseContent = $client->getResponse()->getContent();
        $this->assertNotFalse($responseContent, 'Response content should not be false');
        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Response should be valid JSON array');

        $this->assertArrayHasKey('items', $responseData);
        $this->assertIsArray($responseData['items']);
        $this->assertCount(1, $responseData['items']);
        $this->assertIsArray($responseData['items'][0]);
        $this->assertEquals(5, $responseData['items'][0]['quantity']);
        $this->assertEquals(499.95, $responseData['items'][0]['subtotal']);
        $this->assertEquals(499.95, $responseData['total']);
    }
}
