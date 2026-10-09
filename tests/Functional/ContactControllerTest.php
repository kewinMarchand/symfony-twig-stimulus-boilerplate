<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Contact\Exception\ContactMessageNotSentException;
use App\Domain\Contact\Model\ContactMessage;
use App\Domain\Contact\Sender\ContactMessageSender;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactControllerTest extends WebTestCase
{
    public function testInvalidSubmissionLinksEachErrorToItsField(): void
    {
        $client = $this->submit(['name' => '', 'email' => 'ada', 'message' => 'court']);

        self::assertResponseStatusCodeSame(422);
        $email = $client->getCrawler()->filter('[data-testid="contact-email"]');
        self::assertSame('true', $email->attr('aria-invalid'));
        $describedBy = (string) $email->attr('aria-describedby');
        self::assertStringContainsString('adresse e-mail valide', $client->getCrawler()->filter('#'.$describedBy)->text());
        self::assertNotNull($client->getCrawler()->filter('[data-testid="contact-name"]')->attr('autofocus'));
    }

    public function testValidSubmissionWithoutJavaScriptRedirectsAndConfirms(): void
    {
        $client = $this->submit(['name' => 'Ada', 'email' => 'ada@exemple.fr', 'message' => 'Bonjour, ceci est un message.']);

        self::assertResponseRedirects('/contact', 303);
        $client->followRedirect();
        self::assertSelectorTextContains('[data-testid="contact-success"]', 'bien été envoyé');
    }

    public function testValidSubmissionWithTurboAnswersWithAStream(): void
    {
        $client = $this->submit(['name' => 'Ada', 'email' => 'ada@exemple.fr', 'message' => 'Bonjour, ceci est un message.'], ['HTTP_ACCEPT' => 'text/vnd.turbo-stream.html, text/html']);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/vnd.turbo-stream.html; charset=UTF-8');
        self::assertStringContainsString('target="contact-status"', (string) $client->getResponse()->getContent());
    }

    public function testSendingFailureShowsABusinessMessage(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        self::getContainer()->set(ContactMessageSender::class, new class implements ContactMessageSender {
            public function send(ContactMessage $message): void
            {
                throw new ContactMessageNotSentException();
            }
        });
        $this->submitWith($client, ['name' => 'Ada', 'email' => 'ada@exemple.fr', 'message' => 'Bonjour, ceci est un message.']);

        self::assertResponseStatusCodeSame(503);
        self::assertSelectorTextContains('[data-testid="contact-error"]', 'L’envoi a échoué');
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $server
     */
    private function submit(array $values, array $server = []): KernelBrowser
    {
        $client = self::createClient();
        $this->submitWith($client, $values, $server);

        return $client;
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $server
     */
    private function submitWith(KernelBrowser $client, array $values, array $server = []): void
    {
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->filter('[data-testid="contact-submit"]')->form();
        $client->submit($form, [
            'contact[name]' => $values['name'],
            'contact[email]' => $values['email'],
            'contact[message]' => $values['message'],
        ], ['HTTP_ORIGIN' => 'http://localhost', ...$server]);
    }
}
