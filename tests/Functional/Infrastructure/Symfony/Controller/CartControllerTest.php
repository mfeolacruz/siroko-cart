<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Symfony\Controller;

use App\Domain\Cart\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CartControllerTest extends WebTestCase
{
    public function testCreateCartReturnsCreatedStatusAndCartId(): void
    {
        $client = static::createClient();

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

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertArrayHasKey('created_at', $responseData);
        $this->assertNull($responseData['user_id']);

        $this->assertIsString($responseData['id']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $responseData['id']
        );
    }

    public function testCreateCartWithUserIdStoresUserId(): void
    {
        $client = static::createClient();

        $userId = UserId::generate()->value();

        $client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['user_id' => $userId])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertEquals($userId, $responseData['user_id']);
    }
}
