<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\ValueObject;

use App\Domain\Cart\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function testItGeneratesValidUuid(): void
    {
        $userId = UserId::generate();

        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $userId->value()
        );
    }

    public function testItCreatesFromValidString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $userId = UserId::fromString($uuid);

        $this->assertEquals($uuid, $userId->value());
    }

    public function testItThrowsExceptionForInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UserId::fromString('invalid-uuid');
    }

    public function testItComparesEquality(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId1 = UserId::fromString($uuid);
        $userId2 = UserId::fromString($uuid);
        $userId3 = UserId::generate();

        $this->assertTrue($userId1->equals($userId2));
        $this->assertFalse($userId1->equals($userId3));
    }

    public function testItConvertsToString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($uuid);

        $this->assertEquals($uuid, (string) $userId);
    }
}
