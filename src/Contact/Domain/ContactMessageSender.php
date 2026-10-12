<?php

declare(strict_types=1);

namespace App\Contact\Domain;

interface ContactMessageSender
{
    /**
     * @throws ContactMessageNotSentException
     */
    public function send(ContactMessage $message): void;
}
