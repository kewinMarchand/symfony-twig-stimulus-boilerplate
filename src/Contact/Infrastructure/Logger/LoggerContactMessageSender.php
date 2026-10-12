<?php

declare(strict_types=1);

namespace App\Contact\Infrastructure\Logger;

use App\Contact\Domain\ContactMessage;
use App\Contact\Domain\ContactMessageSender;
use Psr\Log\LoggerInterface;

final readonly class LoggerContactMessageSender implements ContactMessageSender
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function send(ContactMessage $message): void
    {
        $this->logger->info('Message de contact reçu ({length} caractères).', ['length' => mb_strlen($message->message)]);
    }
}
