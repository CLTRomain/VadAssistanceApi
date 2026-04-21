<?php
declare(strict_types=1);

namespace App\Controller;
use Firebase\JWT\JWT;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Mailer\Message;
use Cake\Mailer\TransportFactory;

/**
 * Subscribers Controller
 *
 * @property \App\Model\Table\SubscribersTable $Subscribers
 */
class SubscribersController extends AppController
{
    public $Subscribers;
    public $ContractsSubscribers;
    public $Contract;
    public $ContractsSubscribersFiles;

        public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    
        $this->Subscribers = $this->fetchTable('Subscribers');
        $this->ContractsSubscribers = $this->fetchTable('ContractsSubscribers');
        $this->Contract = $this->fetchTable('Contracts');
        $this->ContractsSubscribersFiles = $this->fetchTable('ContractSubscriberFiles');
        
    }

       public function login()
    {

    // Indispensable pour l'API
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $password = $this->request->getData('password');

      
            // 1. CONTRÔLE : Champs vides
            if (empty($email) || empty($password)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false, 
                        'message' => 'Veuillez remplir tous les champs.'
                    ]));
            }

            // 2. Recherche de l'utilisateur
            $user = $this->Subscribers->find()
                ->where(['email' => $email])
                ->first();

            if (!$user) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false, 
                        'message' => 'Email Incorrect.'
                    ]));
            }

            if (!$user->password) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false, 
                        'message' => 'Votre compte n\'a pas de mot de passe. Veuillez finaliser inscription.'
                    ]));
            }

            // 3. Vérification manuelle du mot de passe
            $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
            
            if ($hasher->check($password, $user->password)) {

                if ($user->active === false) {
                    return $this->response
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false, 
                            'message' => 'Votre compte n\'est pas encore activé.'
                        ]));
                }

               

                // --- AJOUT DE LA GÉNÉRATION JWT ---
                // On récupère le JApiToken que tu as mis dans ton app_local.php
                $payload = [
                    'sub' => $user->id,           // ID de l'utilisateur
                    'email' => $user->email,      // Email (optionnel mais pratique)
                    'iat' => time(),              // Date de création
                    'exp' => time() + (60 * 60 * 24 * 14), // Expire dans 14 jours
                ];

                // On utilise Security.salt ou ta clé spécifique dans app_local
                $jwtKey = Configure::read('App.JWTApiToken'); 

                // Ou si tu l'as nommé différemment : Configure::read('Auth.JwtToken.secret')

                $token = JWT::encode($payload, $jwtKey, 'HS256');   
                
                
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Heureux de vous revoir !',
                        'token' => $token, // ON RENVOIE LE TOKEN ICI
                    ]));
            }

            // 5. Échec final
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false, 
                    'message' => 'Email ou mot de passe incorrect.'
                ]));
        }
    }

public function getprofile()
{

    // On s'assure que la réponse est toujours traitée comme du JSON
    $this->response = $this->response->withType('application/json');

    try {


        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            throw new \Exception("Token manquant", 401);
        }

        $jwtKey = Configure::read('App.JWTApiToken'); 
        $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));
        $userId = $decoded->sub; 

        // 1. Récupération de l'abonné
        $subscriber = $this->Subscribers->get($userId);

        // 2. Récupération de tous les contrats actifs du subscriber
        $subscriberContracts = $this->ContractsSubscribers->find()
            ->where(['subscriber_id' => $userId, 'canceled_at IS' => null])
            ->contain(['Contracts'])
            ->all();

        // 3. Construction du tableau de contrats avec leurs fichiers
        $contractsArray = [];
        foreach ($subscriberContracts as $subscriberContract) {
            $files = $this->ContractsSubscribersFiles->find()
                ->where(['contract_subscriber_id' => $subscriberContract->id])
                ->all();

            foreach ($files as $file) {
                $file->download_path = base64_encode('subscribers/' . $subscriber->id . '/' . $subscriberContract->id . '/' . $file->name . '.pdf');
            }

            $contractsArray[] = [
                'contract_id'             => $subscriberContract->contract_id,
                'contract_subscriber_id'  => $subscriberContract->id,
                'contract_name'           => $subscriberContract->contract ? $subscriberContract->contract->name : 'Contrat inconnu',
                'signed_at'               => $subscriberContract->signed_at ? $subscriberContract->signed_at->format('d/m/Y') : null,
                'files'                   => $files->toArray(),
            ];
        }

        $infoUser = [
            'first_name' => $subscriber->first_name,
            'last_name'  => $subscriber->last_name,
            'date_of_birth' => $subscriber->birth_date ? $subscriber->birth_date->format('Y-m-d') : null,
            'email'      => $subscriber->email,
            'phone'      => $subscriber->phone,
            'address'    => $subscriber->address,
            'contracts'  => $contractsArray,
        ];

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'user_info' => $infoUser 
        ]));

    } catch (\Exception $e) {
        // Loggez l'erreur réelle dans logs/error.log pour vous aider à débugger sans casser le mobile
        $this->log($e->getMessage(), 'error');

        $status = ($e->getCode() === 401) ? 401 : 500;
        
        return $this->response
            ->withStatus($status)
            ->withStringBody(json_encode([
                'success' => false, 
                'message' => 'Erreur lors de la récupération du profil',
                'debug' => (Configure::read('debug')) ? $e->getMessage() : null // Utile en dev
            ]));
    }
}

    public function savePushToken()
    {
        $this->request->allowMethod(['post']);
        $this->response = $this->response->withType('application/json');

        try {
            $authHeader = $this->request->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $authHeader);
            $jwtKey = Configure::read('App.JWTApiToken');
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));
            $subscriberId = $decoded->sub;

            $pushToken = $this->request->getData('push_token');
            if (empty($pushToken)) {
                return $this->response->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'message' => 'push_token manquant']));
            }

            $SubscriberPushTokens = $this->fetchTable('SubscriberPushTokens');

            $existing = $SubscriberPushTokens->find()
                ->where(['push_token' => $pushToken])
                ->first();

            if ($existing) {
                // Token déjà enregistré sur un autre compte → on le réassigne au compte courant
                if ((int)$existing->subscriber_id !== (int)$subscriberId) {
                    $existing->subscriber_id = $subscriberId;
                    $SubscriberPushTokens->save($existing);
                }
                // Si même compte, rien à faire
            } else {
                // Nouveau token → on l'enregistre
                $entry = $SubscriberPushTokens->newEntity([
                    'subscriber_id' => $subscriberId,
                    'push_token'    => $pushToken,
                ]);
                $SubscriberPushTokens->save($entry);
            }

            return $this->response->withStringBody(json_encode(['success' => true]));

        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    public function logout()
    {
        $this->request->allowMethod(['post']);
        $this->response = $this->response->withType('application/json');

        try {
            $authHeader = $this->request->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $authHeader);
            $jwtKey = Configure::read('App.JWTApiToken');
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));
            $subscriberId = $decoded->sub;

            $pushToken = $this->request->getData('push_token');

            if (!empty($pushToken)) {
                $SubscriberPushTokens = $this->fetchTable('SubscriberPushTokens');
                $existing = $SubscriberPushTokens->find()
                    ->where(['push_token' => $pushToken, 'subscriber_id' => $subscriberId])
                    ->first();

                if ($existing) {
                    $SubscriberPushTokens->delete($existing);
                }
            }

            return $this->response->withStringBody(json_encode(['success' => true]));

        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    public function forgotPassword()
    {
        $this->request->allowMethod(['post']);
        $this->response = $this->response->withType('application/json');

        $email = $this->request->getData('email');
        if (empty($email)) {
            return $this->response->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Email manquant']));
        }

        $subscriber = $this->Subscribers->find()
            ->where(['email' => $email])
            ->first();

        // On répond toujours "success" pour ne pas révéler si l'email existe
        if (!$subscriber) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.'
            ]));
        }

        // Génération du token sécurisé
        $token = bin2hex(random_bytes(32));
        $expires = new \Cake\I18n\DateTime('+1 hour');

        $subscriber->lost_password = $token;
        $subscriber->lost_password_validity = $expires;
        $this->Subscribers->save($subscriber);

        // Envoi de l'email
        $resetLink = Configure::read('App.fullBaseUrl') . '/subscribers/reset-password?token=' . $token;
        $firstName = htmlspecialchars($subscriber->first_name);
        $htmlBody = '
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

          <!-- Header orange -->
          <tr>
            <td style="background-color:#f97316;padding:36px 40px;text-align:center;">
              <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:800;letter-spacing:1px;">VAD ASSISTANCE</h1>
              <p style="margin:6px 0 0;color:#ffe8d6;font-size:13px;letter-spacing:2px;text-transform:uppercase;">Votre assistant au quotidien</p>
            </td>
          </tr>

          <!-- Corps -->
          <tr>
            <td style="padding:40px;">
              <h2 style="margin:0 0 16px;color:#1a1a1a;font-size:20px;">Réinitialisation de votre mot de passe</h2>
              <p style="margin:0 0 12px;color:#555555;font-size:15px;line-height:1.6;">Bonjour <strong>' . $firstName . '</strong>,</p>
              <p style="margin:0 0 24px;color:#555555;font-size:15px;line-height:1.6;">
                Vous avez demandé la réinitialisation de votre mot de passe.<br>
                Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.<br>
                Ce lien est valable <strong>1 heure</strong>.
              </p>

              <!-- Bouton -->
              <table cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
                <tr>
                  <td style="background-color:#f97316;border-radius:8px;">
                    <a href="' . $resetLink . '" style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;border-radius:8px;">
                      Réinitialiser mon mot de passe
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 8px;color:#999999;font-size:13px;line-height:1.5;">
                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :
              </p>
              <p style="margin:0 0 32px;word-break:break-all;">
                <a href="' . $resetLink . '" style="color:#f97316;font-size:13px;">' . $resetLink . '</a>
              </p>

              <hr style="border:none;border-top:1px solid #eeeeee;margin:0 0 24px;">

              <p style="margin:0;color:#bbbbbb;font-size:12px;line-height:1.6;">
                Si vous n\'êtes pas à l\'origine de cette demande, ignorez simplement cet email.<br>
                Votre mot de passe restera inchangé.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#fafafa;padding:20px 40px;text-align:center;border-top:1px solid #eeeeee;">
              <p style="margin:0;color:#cccccc;font-size:12px;">
                © ' . date('Y') . ' VAD Assistance — Tous droits réservés
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
        ';

        $message = new Message();
        $message->setFrom(['noreply@mg2.vad-assistance.fr' => 'VAD Assistance'])
            ->setTo($subscriber->email)
            ->setSubject('Réinitialisation de votre mot de passe VAD Assistance')
            ->setEmailFormat('html')
            ->setBodyHtml($htmlBody);

        TransportFactory::get('mailgun')->send($message);

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.'
        ]));
    }

    public function resetPassword()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('text/html');
        $token = $this->request->getQuery('token');

        $message = null;
        $messageType = null;
        $showForm = false;

        if (empty($token)) {
            return $this->response->withStringBody($this->_renderResetPage(false, 'Lien invalide ou expiré.', 'error', ''));
        }

        $subscriber = $this->Subscribers->find()
            ->where(['lost_password' => $token])
            ->first();

        if (!$subscriber || $subscriber->lost_password_validity < new \Cake\I18n\DateTime()) {
            return $this->response->withStringBody($this->_renderResetPage(false, 'Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.', 'error', $token));
        }

        if ($this->request->is('post')) {
            $password = $this->request->getData('password');
            $confirm  = $this->request->getData('password_confirm');

            if (strlen($password) < 8) {
                return $this->response->withStringBody($this->_renderResetPage(true, 'Le mot de passe doit contenir au moins 8 caractères.', 'error', $token));
            }
            if ($password !== $confirm) {
                return $this->response->withStringBody($this->_renderResetPage(true, 'Les mots de passe ne correspondent pas.', 'error', $token));
            }

            $subscriber->password = $password;
            $subscriber->lost_password = null;
            $subscriber->lost_password_validity = null;
            $this->Subscribers->save($subscriber);

            return $this->response->withStringBody($this->_renderResetPage(false, 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez vous connecter dans l\'application.', 'success', $token));
        }

        return $this->response->withStringBody($this->_renderResetPage(true, null, null, $token));
    }

    private function _renderResetPage(bool $showForm, ?string $message, ?string $messageType, string $token): string
    {
        $messageHtml = '';
        if ($message) {
            $color = $messageType === 'success' ? '#16a34a' : '#dc2626';
            $bg    = $messageType === 'success' ? '#f0fdf4' : '#fef2f2';
            $border = $messageType === 'success' ? '#bbf7d0' : '#fecaca';
            $messageHtml = '<div style="padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px;background:' . $bg . ';color:' . $color . ';border:1px solid ' . $border . ';">' . htmlspecialchars($message) . '</div>';
        }

        $formHtml = '';
        if ($showForm) {
            $formHtml = '
            <h2 style="margin:0 0 8px;color:#1a1a1a;font-size:20px;">Nouveau mot de passe</h2>
            <p style="margin:0 0 24px;color:#666;font-size:14px;">Choisissez un mot de passe sécurisé d\'au moins 8 caractères.</p>
            <form method="POST" action="/subscribers/reset-password?token=' . htmlspecialchars($token) . '">
                <label style="display:block;font-size:14px;font-weight:500;color:#333;margin-bottom:6px;">Nouveau mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required minlength="8"
                    style="width:100%;padding:12px 16px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:15px;margin-bottom:16px;box-sizing:border-box;">
                <label style="display:block;font-size:14px;font-weight:500;color:#333;margin-bottom:6px;">Confirmer le mot de passe</label>
                <input type="password" name="password_confirm" placeholder="••••••••" required minlength="8"
                    style="width:100%;padding:12px 16px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:15px;margin-bottom:24px;box-sizing:border-box;">
                <button type="submit"
                    style="width:100%;padding:13px;background:#f97316;color:white;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;">
                    Réinitialiser le mot de passe
                </button>
            </form>';
        }

        return '<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Réinitialisation - VAD Assistance</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:60px 20px;">
    <tr><td align="center">
      <table width="460" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
        <tr>
          <td style="background:#f97316;padding:30px 40px;text-align:center;">
            <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:1px;">VAD ASSISTANCE</div>
            <div style="color:#ffe8d6;font-size:11px;letter-spacing:2px;margin-top:4px;text-transform:uppercase;">Votre assistant au quotidien</div>
          </td>
        </tr>
        <tr>
          <td style="padding:36px 40px;">
            ' . $messageHtml . $formHtml . '
          </td>
        </tr>
        <tr>
          <td style="background:#fafafa;padding:16px 40px;text-align:center;border-top:1px solid #eee;">
            <p style="margin:0;color:#ccc;font-size:12px;">© ' . date('Y') . ' VAD Assistance — Tous droits réservés</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';
    }

    public function editinfo() {

        // recuperation du subscriberID dans le token depuis le header
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        $jwtKey = Configure::read('App.JWTApiToken'); 
        $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));

        $subscriberId = $decoded->sub; 


        $subscriber = $this->Subscribers->get($subscriberId);


        $newData = $this->request->getData();
        $subscriber = $this->Subscribers->patchEntity($subscriber, $newData, [
            'fields' => ['email', 'phone', 'address']
        ]);

        // 4. Vérification : Est-ce que quelque chose a réellement changé ?
        if ($subscriber->isDirty()) {
        
            if ($subscriber->isDirty('email')) {                
                // 2. On EFFACE la date d'activation
                $subscriber->activated_account = null; 
            } else if ($subscriber->isDirty('address')) {
                $subscriber->address_rest = ""; 
                $subscriber->postal_code = ""; 
                $subscriber->city = "";
                $subscriber->address_rest = "";

            }

              if ($this->Subscribers->save($subscriber)) {
                $status = 'success';
                $message = 'Informations mises à jour avec succès.';
            } else {
                $status = 'error';
                $message = 'Erreur lors de la sauvegarde.';
            }

              return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => $status ,
                'message' => $message
            ]));

        } else {
           return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => false,
                'message' => 'Aucune modification effectuée.'
            ]));
        }



    }
}
