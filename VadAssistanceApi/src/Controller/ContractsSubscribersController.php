<?php
declare(strict_types=1);

namespace App\Controller;
use Firebase\JWT\JWT;
use Cake\Core\Configure;

/**
 * ContractsSubscribers Controller
 *
 * @property \App\Model\Table\ContractsSubscribersTable $ContractsSubscribers
 */
class ContractsSubscribersController extends AppController
{

    public $contractSubscriberFiles;
    public $contractsSuscribers;
    public $demands;
    public $SupportRequest;
    public $SupportRequestContacts;
    public $SupportRequestsSkills;
    public $Skills;

    

    public function initialize(): void
    {
        parent::initialize();

        $this->contractSubscriberFiles = $this->fetchTable('ContractSubscriberFiles');
        $this->contractsSuscribers = $this->fetchTable('ContractsSubscribers');
        $this->demands = $this->fetchTable('Demands');
        $this->SupportRequest = $this->fetchTable('SupportRequests');
        $this->SupportRequestContacts = $this->fetchTable('SupportRequestContacts');
        $this->SupportRequestsSkills = $this->fetchTable('SupportRequestsSkills');
        $this->Skills = $this->fetchTable('Skills');

     

    }

public function getContractDetails($id = null)
{   
        $id = '21'; // ID en dur parce qu'il y a qu'un seul contrat pour le moment, à remplacer par une variable dynamique plus tard
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            throw new \Exception("Token manquant", 401);
        }

        $jwtKey = Configure::read('App.JWTApiToken'); 
        $decoded = JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));
        $subscribers_id = $decoded->sub;


    $this->response = $this->response->withType('application/json');

    try {
    
        if (!$subscribers_id) {
            return $this->response->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']));
        }

        $contractData = Configure::read('App.contracts.v1.' . $id);

        
        // --- VOTRE LOGIQUE DE BASE ---
        $ContractsSubscribed = $this->ContractsSubscribers->find()
            ->where([
                'subscriber_id' => $subscribers_id,
                'canceled_at IS' => null
            ])
            ->first();

        if (!$ContractsSubscribed) {
            return $this->response->withStringBody(json_encode([
                'success' => true, 
                'finalResults' => [], 
                'message' => 'Aucun contrat actif'
            ]));
        }

        $createdDate = $ContractsSubscribed['created'];
        $currentDate = new \Cake\I18n\FrozenTime();
        $yearsElapsed = $createdDate->diff($currentDate)->y + 1;

        $capAmount = 1500;
        $finalResults = [];

        foreach ($contractData as $domain => $annualCredit) {
            if ($domain === 'cumul') continue;

            $cumul = min($capAmount, $annualCredit * $yearsElapsed);
            $totalSinistres = 0;
            $sinistresDetails = [];

            $SupportRequestContacts = $this->SupportRequestContacts->find()
                ->where(['subscriber_id' => $subscribers_id])
                ->toArray();

            $SupportRequests = $this->SupportRequest->find()
                ->where(['contract_subscriber_id' => $subscribers_id])
                ->toArray();

            foreach ($SupportRequests as $Request) {
                $SupportRequestSkills = $this->SupportRequestsSkills->find()
                    ->where(['support_request_id' => $Request->id])
                    ->toArray();
                
                foreach ($SupportRequestSkills as $skillData) {
                    $Skill = $this->Skills->find()
                        ->where(['id' => $skillData->skill_id])
                        ->first();

                    if (!$Skill || $Skill->slug !== $domain) continue;

                    $paymentDate = new \Cake\I18n\FrozenTime($skillData->payment_date);
                    $startDate = $ContractsSubscribed->last_reset_date ?? $createdDate;

                    $paymentWithTax = 0;
                    if ($paymentDate >= $startDate) {
                        $paymentWithTax = round($skillData->payment_amount * (1 + $skillData->tax / 100), 2);
                        $totalSinistres += $paymentWithTax;
                    }

                    $sinistresDetails[] = [
                        'skill_name' => $Skill->name,
                        'payment_dest' => $skillData->payment_dest,
                        'payment_amount' => (floor($paymentWithTax) == $paymentWithTax) ? $paymentWithTax : number_format($paymentWithTax, 2, '.', ''),
                        'tax' => $skillData->tax,
                        'payment_date' => $paymentDate->i18nFormat('dd/MM/yyyy')
                    ];
                }
            }

            $finalResults[] = [
                'idContractSubscribers' => $ContractsSubscribed->id,
                'domain' => $domain,
                'cumul' => $cumul,
                'totalSinistres' => $totalSinistres,
                'balance' => max(0, $cumul - $totalSinistres),
                'sinistresDetails' => $sinistresDetails
            ];
        }

        // --- SORTIE API ---
        return $this->response->withStringBody(json_encode([
            'success' => true,
            'data' => [
                'finalResults' => $finalResults,
                'supportRequestContacts' => $SupportRequestContacts
            ]
        ]));

    } catch (\Exception $e) {
        return $this->response->withStatus(500)
            ->withStringBody(json_encode([
                'success' => false, 
                'message' => 'Erreur serveur',
                'error' => Configure::read('debug') ? $e->getMessage() : null
            ]));
    }
}
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ContractsSubscribers->find()
            ->contain(['Subscribers', 'Contracts', 'EndedReasons', 'CanceledReasons', 'Sellers', 'Admins', 'QCUser']);
        $contractsSubscribers = $this->paginate($query);

        $this->set(compact('contractsSubscribers'));
    }

    /**
     * View method
     *
     * @param string|null $id Contracts Subscriber id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $contractsSubscriber = $this->ContractsSubscribers->get($id, contain: ['Subscribers', 'Contracts', 'EndedReasons', 'CanceledReasons', 'Sellers', 'Admins', 'QCUser', 'Call2Comments', 'ProofFile', 'Comments', 'Files', 'FilesToSign', 'QualityFiles', 'NotAudioFiles', 'AudioFiles', 'Mailings', 'Logs', 'PaymentDebts', 'ContractsSubscribersUnpaids', 'RefundRequests']);
        $this->set(compact('contractsSubscriber'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $contractsSubscriber = $this->ContractsSubscribers->newEmptyEntity();
        if ($this->request->is('post')) {
            $contractsSubscriber = $this->ContractsSubscribers->patchEntity($contractsSubscriber, $this->request->getData());
            if ($this->ContractsSubscribers->save($contractsSubscriber)) {
                $this->Flash->success(__('The contracts subscriber has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The contracts subscriber could not be saved. Please, try again.'));
        }
        $subscribers = $this->ContractsSubscribers->Subscribers->find('list', limit: 200)->all();
        $contracts = $this->ContractsSubscribers->Contracts->find('list', limit: 200)->all();
        $endedReasons = $this->ContractsSubscribers->EndedReasons->find('list', limit: 200)->all();
        $canceledReasons = $this->ContractsSubscribers->CanceledReasons->find('list', limit: 200)->all();
        $sellers = $this->ContractsSubscribers->Sellers->find('list', limit: 200)->all();
        $admins = $this->ContractsSubscribers->Admins->find('list', limit: 200)->all();
        $qCUser = $this->ContractsSubscribers->QCUser->find('list', limit: 200)->all();
        $this->set(compact('contractsSubscriber', 'subscribers', 'contracts', 'endedReasons', 'canceledReasons', 'sellers', 'admins', 'qCUser'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Contracts Subscriber id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $contractsSubscriber = $this->ContractsSubscribers->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $contractsSubscriber = $this->ContractsSubscribers->patchEntity($contractsSubscriber, $this->request->getData());
            if ($this->ContractsSubscribers->save($contractsSubscriber)) {
                $this->Flash->success(__('The contracts subscriber has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The contracts subscriber could not be saved. Please, try again.'));
        }
        $subscribers = $this->ContractsSubscribers->Subscribers->find('list', limit: 200)->all();
        $contracts = $this->ContractsSubscribers->Contracts->find('list', limit: 200)->all();
        $endedReasons = $this->ContractsSubscribers->EndedReasons->find('list', limit: 200)->all();
        $canceledReasons = $this->ContractsSubscribers->CanceledReasons->find('list', limit: 200)->all();
        $sellers = $this->ContractsSubscribers->Sellers->find('list', limit: 200)->all();
        $admins = $this->ContractsSubscribers->Admins->find('list', limit: 200)->all();
        $qCUser = $this->ContractsSubscribers->QCUser->find('list', limit: 200)->all();
        $this->set(compact('contractsSubscriber', 'subscribers', 'contracts', 'endedReasons', 'canceledReasons', 'sellers', 'admins', 'qCUser'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Contracts Subscriber id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $contractsSubscriber = $this->ContractsSubscribers->get($id);
        if ($this->ContractsSubscribers->delete($contractsSubscriber)) {
            $this->Flash->success(__('The contracts subscriber has been deleted.'));
        } else {
            $this->Flash->error(__('The contracts subscriber could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
