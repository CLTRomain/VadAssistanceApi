<?php
declare(strict_types=1);

namespace App\Controller;

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
    $this->loadComponent('Authentication.Authentication');
    $this->Authentication->allowUnauthenticated(['login']);

   
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
                
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Heureux de vous revoir !',
                        'data' => ['user' => $user]
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
}
