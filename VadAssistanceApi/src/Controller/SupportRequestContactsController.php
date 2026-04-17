<?php
declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Cake\Core\Configure;

class SupportRequestContactsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    /**
     * Crée une nouvelle demande d'intervention.
     * POST /DemandsInterventions
     * Body : { "subject": "...", "text": "..." }
     */
    public function demandsInterventions()
    {
        $this->request->allowMethod(['post']);
        $this->response = $this->response->withType('application/json');

        try {
            // Récupération du subscriber via JWT
            $authHeader = $this->request->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $authHeader);
            $jwtKey = Configure::read('App.JWTApiToken');
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));
            $subscriberId = $decoded->sub;

            // Récupération du contrat actif du subscriber
            $ContractsSubscribers = $this->fetchTable('ContractsSubscribers');
            $contract = $ContractsSubscribers->find()
                ->where(['subscriber_id' => $subscriberId, 'canceled_at IS' => null])
                ->first();

            if (!$contract) {
                return $this->response->withStatus(404)
                    ->withStringBody(json_encode(['success' => false, 'message' => 'Aucun contrat actif trouvé']));
            }

            $subject = $this->request->getData('subject');
            $text    = $this->request->getData('description');

            if (empty($subject) || empty($text)) {
                return $this->response->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'message' => 'subject et description sont obligatoires']));
            }

            $now = new \Cake\I18n\DateTime();

            $SupportRequestContacts = $this->fetchTable('SupportRequestContacts');
            $contact = $SupportRequestContacts->newEntity([
                'subscriber_id' => $subscriberId,
                'contract_id'   => $contract->contract_id,
                'subject'       => $subject,
                'text'          => $text,
                'status'        => 'pending',
                'hide'          => false,
                'created_at'    => $now,
                'modified_at'   => $now,
            ]);

            if (!$SupportRequestContacts->save($contact)) {
                return $this->response->withStatus(500)
                    ->withStringBody(json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde', 'errors' => $contact->getErrors()]));
            }

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Demande créée avec succès',
                'data'    => ['id' => $contact->id]
            ]));

        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    /**
     * Met à jour le statut d'une demande et envoie une notification push au subscriber.
     * POST /support-request-contacts/{id}/status
     * Body : { "status": "processing"|"closed" }
     */
    public function updateStatus($id = null)
    {
        $this->request->allowMethod(['post']);
        $this->response = $this->response->withType('application/json');

        try {
            $SupportRequestContacts = $this->fetchTable('SupportRequestContacts');
            $contact = $SupportRequestContacts->get($id);

            $newStatus = $this->request->getData('status');
            if (empty($newStatus)) {
                return $this->response->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'message' => 'status manquant']));
            }

            $contact->status = $newStatus;
            $SupportRequestContacts->save($contact);

            // Récupération du push_token du subscriber
            $subscriber = $this->fetchTable('Subscribers')->get($contact->subscriber_id);

            if (!empty($subscriber->push_token)) {
                $this->sendPushNotification($subscriber->push_token, $newStatus);
            }

            return $this->response->withStringBody(json_encode(['success' => true]));

        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    private function sendPushNotification(string $pushToken, string $status): void
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
        curl_exec($ch);
        curl_close($ch);
    }
}
