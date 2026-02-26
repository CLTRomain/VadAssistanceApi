<?php
declare(strict_types=1);

namespace App\Controller;
use Firebase\JWT\JWT;
use Cake\Core\Configure;

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
        // 1. On récupère le token pur depuis le header "Authorization"
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // 2. On utilise la clé publique pour décoder le JWT
            // (Assure-toi d'avoir importé : use Firebase\JWT\JWT; use Firebase\JWT\Key;)
            $jwtKey = Configure::read('App.JWTApiToken'); 
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));

            // 3. ICI TU RÉCUPÈRES LE SUB
            $userId = $decoded->sub; 

            // 4. Tu cherches l'utilisateur en base de données
            $subscriber = $this->Subscribers->get($userId);

            $SubscriberContract = $this->ContractsSubscribers->find()
                ->where(['subscriber_id' => $userId])
                ->contain(['Contracts'])
                ->first();


            $Contract = $this->Contract->find()
                ->where(['id' => $SubscriberContract->contract_id])
                ->first();

            $contractSubscriberFile = $this->ContractsSubscribersFiles->find()
                ->where(['contract_subscriber_id' => $SubscriberContract->id])
                ->all();

             // 2. On boucle sur chaque entité "File" pour lui ajouter le champ
            foreach ($contractSubscriberFile as $file) {

                $file->download_path = base64_encode('subscribers/'. $subscriber->id . '/' .$SubscriberContract->id. '/' . $file->name . '.pdf');
            }

            
            $infoUser = [
                'first_name' => $subscriber->first_name,
                'last_name' => $subscriber->last_name,
                'date_of_birth' => $subscriber->birth_date,
                'email' => $subscriber->email,
                'phone' => $subscriber->phone,
                'address' => $subscriber->address,
                'contract_name' => $Contract->name,
                'contract_subscriber_files' => $contractSubscriberFile->toArray()
            ];

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'user_info' => $infoUser 
                ]));

        } catch (\Exception $e) {
            // Si le token est invalide ou expiré
            return $this->response
                ->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Token invalide']));
        }
    }
}
