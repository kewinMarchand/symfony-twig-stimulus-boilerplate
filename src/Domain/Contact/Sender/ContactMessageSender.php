<?php

declare(strict_types=1);

namespace App\Domain\Contact\Sender;

use App\Domain\Contact\Exception\ContactMessageNotSentException;
use App\Domain\Contact\Model\ContactMessage;

interface ContactMessageSender
{
    /**
     * @throws ContactMessageNotSentException
     */
    public function send(ContactMessage $message): void;
}
