<?php

declare(strict_types=1);

namespace App\Contact\Infrastructure\Http;

use App\Contact\Domain\ContactMessage;
use App\Shared\Infrastructure\Http\ApiPayload;

final class ContactTranslation
{
    /**
     * @return array{name: string, email: string, message: string}
     */
    public static function request(ContactMessage $message): array
    {
        return ['name' => $message->name, 'email' => $message->email, 'message' => $message->message];
    }

    /**
     * Violations d'un 422 de l'API (`violations[].propertyPath`, `message`).
     *
     * @return list<array{field: string, message: string}>
     */
    public static function violations(mixed $payload): array
    {
        return array_map(
            static fn (array $violation): array => ['field' => ApiPayload::string($violation, 'propertyPath'), 'message' => ApiPayload::string($violation, 'message')],
            ApiPayload::objects(ApiPayload::object($payload, 'contact_message')['violations'] ?? null, 'violations'),
        );
    }
}
