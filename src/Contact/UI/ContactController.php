<?php

declare(strict_types=1);

namespace App\Contact\UI;

use App\Contact\Domain\ContactMessageNotSentException;
use App\Contact\Domain\ContactMessageRejectedException;
use App\Contact\Domain\ContactMessageSender;
use App\Contact\UI\Form\ContactFormData;
use App\Contact\UI\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, ContactMessageSender $sender): Response
    {
        $data = new ContactFormData();
        $form = $this->createForm(ContactType::class, $data);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('contact/index.html.twig', ['form' => $form, 'send_error' => null]);
        }

        try {
            $sender->send($data->toMessage());
        } catch (ContactMessageRejectedException $exception) {
            foreach ($exception->violations as $violation) {
                $target = $form->has($violation['field']) ? $form->get($violation['field']) : $form;
                $target->addError(new FormError($violation['message']));
            }

            return $this->render('contact/index.html.twig', ['form' => $form, 'send_error' => null], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        } catch (ContactMessageNotSentException $exception) {
            return $this->render('contact/index.html.twig', [
                'form' => $form,
                'send_error' => $exception->getMessage(),
            ], new Response(status: Response::HTTP_SERVICE_UNAVAILABLE));
        }

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('contact/success.stream.html.twig', [
                'form' => $this->createForm(ContactType::class, new ContactFormData()),
            ]);
        }

        $this->addFlash('contact_success', 'Merci, votre message a bien été envoyé.');

        return $this->redirectToRoute('contact', status: Response::HTTP_SEE_OTHER);
    }
}
