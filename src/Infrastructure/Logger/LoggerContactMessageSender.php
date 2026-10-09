<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use App\Domain\Contact\Model\ContactMessage;
use App\Domain\Contact\Sender\ContactMessageSender;
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
