<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ProcessCheckoutControllerTest extends WebTestCase
{
    public function testProcessCheckoutReturnsCreatedStatusAndOrderId(): void
    {
        $client = static::createClient();

        // First, create a cart
        $client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $createCartResponse = json_decode($content, true);
        $this->assertIsArray($createCartResponse);
        $this->assertArrayHasKey('id', $createCartResponse);
        $this->assertIsString($createCartResponse['id']);
        $cartId = $createCartResponse['id'];

        // Add an item to the cart
        $client->request(
            'POST',
            sprintf('/api/carts/%s/items', $cartId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'product_id' => '550e8400-e29b-41d4-a716-446655440001',
                'product_name' => 'Test Product',
                'unit_price' => 29.99,
                'currency' => 'EUR',
                'quantity' => 2,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Now process the checkout
        $client->request(
            'POST',
            sprintf('/api/carts/%s/checkout', $cartId)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('order_id', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Order created successfully', $responseData['message']);

        $this->assertIsString($responseData['order_id']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $responseData['order_id']
        );
    }

    public function testProcessCheckoutReturnsNotFoundWhenCartDoesNotExist(): void
    {
        $client = static::createClient();

        $nonExistentCartId = '550e8400-e29b-41d4-a716-446655440999';

        $client->request(
            'POST',
            sprintf('/api/carts/%s/checkout', $nonExistentCartId)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsArray($responseData['error']);
        $this->assertEquals('cart_not_found', $responseData['error']['code']);
        $this->assertEquals('Cart not found', $responseData['error']['message']);
    }

    public function testProcessCheckoutReturnsBadRequestWhenCartIsEmpty(): void
    {
        $client = static::createClient();

        // First, create an empty cart
        $client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $createCartResponse = json_decode($content, true);
        $this->assertIsArray($createCartResponse);
        $this->assertArrayHasKey('id', $createCartResponse);
        $this->assertIsString($createCartResponse['id']);
        $cartId = $createCartResponse['id'];

        // Try to checkout empty cart
        $client->request(
            'POST',
            sprintf('/api/carts/%s/checkout', $cartId)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsArray($responseData['error']);
        $this->assertEquals('empty_cart', $responseData['error']['code']);
        $this->assertEquals('Cannot checkout empty cart', $responseData['error']['message']);
    }

    public function testProcessCheckoutReturnsBadRequestWithInvalidCartId(): void
    {
        $client = static::createClient();

        $invalidCartId = 'invalid-cart-id';

        $client->request(
            'POST',
            sprintf('/api/carts/%s/checkout', $invalidCartId)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsArray($responseData['error']);
        $this->assertEquals('invalid_cart_id', $responseData['error']['code']);
    }
}
