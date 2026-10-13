<?php

declare(strict_types=1);

namespace App\Contact\Domain;

interface ContactMessageSender
{
    /**
     * @throws ContactMessageNotSentException  l'envoi a échoué (réseau, erreur du destinataire)
     * @throws ContactMessageRejectedException le destinataire refuse le message, champ par champ
     */
    public function send(ContactMessage $message): void;
}
