<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class SendPushNotificationsCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $SupportRequestContacts = $this->fetchTable('SupportRequestContacts');
        $Subscribers = $this->fetchTable('Subscribers');

        // On récupère toutes les demandes dont le statut a changé mais la notif n'a pas encore été envoyée
        $pending = $SupportRequestContacts->find()
            ->where(['push_sent' => 0, 'status !=' => 'pending'])
            ->all();

        if ($pending->isEmpty()) {
            $io->out('[PushNotif] Aucune notification à envoyer.');
            return Command::CODE_SUCCESS;
        }

        $SubscriberPushTokens = $this->fetchTable('SubscriberPushTokens');

        foreach ($pending as $contact) {
            try {
                // Récupération de tous les tokens de ce subscriber
                $tokens = $SubscriberPushTokens->find()
                    ->where(['subscriber_id' => $contact->subscriber_id])
                    ->all();

                if ($tokens->isEmpty()) {
                    $io->out("[PushNotif] Subscriber #{$contact->subscriber_id} sans token, ignoré.");
                    $contact->push_sent = 1;
                    $contact->push_sent_status = 'no_token';
                    $SupportRequestContacts->save($contact);
                    continue;
                }

                foreach ($tokens as $tokenEntry) {
                    $this->sendPushNotification($tokenEntry->push_token, $contact->status, $io);
                }

                $contact->push_sent = 1;
                $contact->push_sent_status = $contact->status;
                $SupportRequestContacts->save($contact);

                $io->success("[PushNotif] Notif envoyée → Subscriber #{$contact->subscriber_id} ({$tokens->count()} appareil(s), statut: {$contact->status})");

            } catch (\Exception $e) {
                $io->error("[PushNotif] Erreur pour contact #{$contact->id} : " . $e->getMessage());
            }
        }

        return Command::CODE_SUCCESS;
    }

    private function sendPushNotification(string $pushToken, string $status, ConsoleIo $io): void
    {
        $messages = [
            'processing' => 'Votre demande est en cours de traitement',
            'closed'     => 'Votre demande a été clôturée',
        ];

        $body = $messages[$status] ?? 'Votre demande a été mise à jour';

        $payload = json_encode([[
            'to'    => $pushToken,
            'title' => 'VAD Assistance',
            'body'  => $body,
            'data'  => ['status' => $status],
        ]]);

        $ch = curl_init('https://exp.host/--/api/v2/push/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);

        $io->out('[PushNotif] Réponse Expo : ' . $result);
    }
}
