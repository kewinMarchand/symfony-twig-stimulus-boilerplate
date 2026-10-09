<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\UI\Http\Form\ContactFormData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContactFormDataTest extends KernelTestCase
{
    public function testAcceptsCompleteMessage(): void
    {
        $data = new ContactFormData();
        $data->name = 'Ada';
        $data->email = 'ada@exemple.fr';
        $data->message = 'Bonjour, ceci est un message.';

        self::assertCount(0, $this->validator()->validate($data));
    }

    public function testRejectsInvalidEmailWithFrenchMessage(): void
    {
        $data = new ContactFormData();
        $data->name = 'Ada';
        $data->email = 'ada';
        $data->message = 'Bonjour à tous';

        $violations = $this->validator()->validate($data);

        self::assertCount(1, $violations);
        self::assertStringContainsString('adresse e-mail valide', (string) $violations->get(0)->getMessage());
    }

    public function testRejectsWhitespaceOnlyName(): void
    {
        $data = new ContactFormData();
        $data->name = '   ';
        $data->email = 'ada@exemple.fr';
        $data->message = 'Bonjour, ceci est un message.';

        $violations = $this->validator()->validate($data);

        self::assertCount(1, $violations);
        self::assertSame('name', $violations->get(0)->getPropertyPath());
    }

    private function validator(): ValidatorInterface
    {
        return self::getContainer()->get(ValidatorInterface::class);
    }
}
