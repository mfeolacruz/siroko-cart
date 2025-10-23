<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\CreateCartCommand;
use PHPUnit\Framework\TestCase;

final class CreateCartCommandTest extends TestCase
{
    public function testItCreatesCommandWithoutUserId(): void
    {
        $command = new CreateCartCommand(null);

        $this->assertNull($command->userId);
    }

    public function testItCreatesCommandWithUserId(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $command = new CreateCartCommand($userId);

        $this->assertEquals($userId, $command->userId);
    }
}
