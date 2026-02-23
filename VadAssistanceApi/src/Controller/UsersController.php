<?php
declare(strict_types=1);

namespace App\Controller;
use Firebase\JWT\JWT;
use Cake\Core\Configure;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public $Subscribers;

public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Flash');
   
    $this->Subscribers = $this->fetchTable('Subscribers');

}
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Users->find()
            ->contain(['Roles', 'Parents']);
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, contain: ['Roles', 'Parents', 'Modules', 'Partnerships', 'Comments', 'LogUpdates', 'LogUsers', 'Subscribers', 'ChildUsers', 'ActiveSellers', 'AllSellers', 'ContractsSubscribers', 'EndedPartnerships', 'Files', 'ArtisanFiles', 'Webpushes']);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $roles = $this->Users->Roles->find('list', limit: 200)->all();
        $parents = $this->Users->Parents->find('list', limit: 200)->all();
        $modules = $this->Users->Modules->find('list', limit: 200)->all();
        $this->set(compact('user', 'roles', 'parents', 'modules'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, contain: ['Modules']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $roles = $this->Users->Roles->find('list', limit: 200)->all();
        $parents = $this->Users->Parents->find('list', limit: 200)->all();
        $modules = $this->Users->Modules->find('list', limit: 200)->all();
        $this->set(compact('user', 'roles', 'parents', 'modules'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
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

                // 4. CONNEXION RÉUSSIE
                $this->Authentication->setIdentity($user);

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


            dd($userId);
            
            // Optionnel : tu peux aussi récupérer le subscription_id si tu l'as mis dedans
            $subscriptionId = $decoded->subscription_id ?? null;

            // 4. Tu cherches l'utilisateur en base de données
            $user = $this->Users->get($userId);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'user' => $user,
                    'sub_found' => $userId // Pour confirmer que ça marche
                ]));

        } catch (\Exception $e) {
            // Si le token est invalide ou expiré
            return $this->response
                ->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Token invalide']));
        }
    }
}
