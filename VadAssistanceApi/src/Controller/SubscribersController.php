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

        // 2. Récupération du lien contrat (utilisez l'option first() prudemment)
        $subscriberContract = $this->ContractsSubscribers->find()
            ->where(['subscriber_id' => $userId])
            ->contain(['Contracts'])
            ->first();

        // Initialisation des données par défaut si pas de contrat
        $contractName = "Aucun contrat";
        $filesArray = [];

        if ($subscriberContract) {
            // On récupère le nom via la relation déjà chargée par contain()
            $contractName = $subscriberContract->contract ? $subscriberContract->contract->name : "Contrat inconnu";

            // 3. Récupération des fichiers
            $files = $this->ContractsSubscribersFiles->find()
                ->where(['contract_subscriber_id' => $subscriberContract->id])
                ->all();

            foreach ($files as $file) {
                $file->download_path = base64_encode('subscribers/'. $subscriber->id . '/' .$subscriberContract->id. '/' . $file->name . '.pdf');
            }
            $filesArray = $files->toArray();
        }

        $infoUser = [
            'first_name' => $subscriber->first_name,
            'last_name' => $subscriber->last_name,
            'date_of_birth' => $subscriber->birth_date ? $subscriber->birth_date->format('Y-m-d') : null,
            'email' => $subscriber->email,
            'phone' => $subscriber->phone,
            'address' => $subscriber->address,
            'contract_name' => $contractName,
            'contract_subscriber_files' => $filesArray
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
