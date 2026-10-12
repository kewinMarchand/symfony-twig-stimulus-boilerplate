<?php

declare(strict_types=1);

namespace App\Tests\Unit\Task\Domain;

use App\Task\Domain\InvalidTaskTitleException;
use App\Task\Domain\Task;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testTrimsTitle(): void
    {
        $task = new Task('1', '  Écrire les tests  ');

        self::assertSame('Écrire les tests', $task->title);
        self::assertFalse($task->done);
    }

    public function testRejectsBlankTitle(): void
    {
        $this->expectException(InvalidTaskTitleException::class);
        $this->expectExceptionMessage('ne peut pas être vide');

        new Task('1', '   ');
    }
}
