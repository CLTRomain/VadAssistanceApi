<?php
declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Cake\Core\Configure;

/**
 * Demands Controller
 *
 * @property \App\Model\Table\DemandsTable $Demands
 */
class DemandsController extends AppController
{
    /**
     * Crée une demande de résiliation.
     * POST /askToEndContract
     * Authorization: Bearer {token}
     */
    public function askToEndContract()
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

            // Récupération des infos du subscriber
            $subscriber = $this->fetchTable('Subscribers')->get($subscriberId);

            // Récupération du contrat actif pour la date de signature
            $contract = $this->fetchTable('ContractsSubscribers')->find()
                ->where(['subscriber_id' => $subscriberId, 'canceled_at IS' => null])
                ->first();

            // Vérification si une demande de résiliation existe déjà
            $existing = $this->Demands->find()
                ->where([
                    'type'       => 'resiliation',
                    'email'      => $subscriber->email,
                ])
                ->first();

            if ($existing) {
                return $this->response->withStatus(409)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Vous avez déjà fait une demande de résiliation.'
                    ]));
            }

            $clientInfo = json_encode([
                'last_name'  => $subscriber->last_name,
                'first_name' => $subscriber->first_name,
                'phone'      => $subscriber->phone,
                'email'      => $subscriber->email,
                'birth_date' => $subscriber->birth_date ? $subscriber->birth_date->format('Y-m-d') : null,
            ], JSON_UNESCAPED_UNICODE);

            $contractId = $contract ? $contract->id : 'N/A';

            $description = "DEMANDE DE RÉSILIATION\n"
                . "----------------------\n"
                . "Infos client : {$clientInfo}\n"
                . "ID du contrat : {$contractId}";

            $demand = $this->Demands->newEntity([
                'type'        => 'resiliation',
                'first_name'  => $subscriber->first_name,
                'last_name'   => $subscriber->last_name,
                'email'       => $subscriber->email,
                'phone'       => $subscriber->phone,
                'description' => $description,
                'status'      => 0,
                'rgpd_consent' => 1,
            ]);

            if (!$this->Demands->save($demand)) {
                return $this->response->withStatus(500)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de la sauvegarde',
                        'errors'  => $demand->getErrors()
                    ]));
            }

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Demande de résiliation enregistrée',
                'data'    => ['id' => $demand->id]
            ]));

        } catch (\Exception $e) {
            return $this->response->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Demands->find();
        $demands = $this->paginate($query);

        $this->set(compact('demands'));
    }

    /**
     * View method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $demand = $this->Demands->get($id, contain: []);
        $this->set(compact('demand'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $demand = $this->Demands->newEmptyEntity();
        if ($this->request->is('post')) {
            $demand = $this->Demands->patchEntity($demand, $this->request->getData());
            if ($this->Demands->save($demand)) {
                $this->Flash->success(__('The demand has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The demand could not be saved. Please, try again.'));
        }
        $this->set(compact('demand'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $demand = $this->Demands->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $demand = $this->Demands->patchEntity($demand, $this->request->getData());
            if ($this->Demands->save($demand)) {
                $this->Flash->success(__('The demand has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The demand could not be saved. Please, try again.'));
        }
        $this->set(compact('demand'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $demand = $this->Demands->get($id);
        if ($this->Demands->delete($demand)) {
            $this->Flash->success(__('The demand has been deleted.'));
        } else {
            $this->Flash->error(__('The demand could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
