<?php

declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Defuse\Crypto\Key;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\I18n\FrozenTime;
use Defuse\Crypto\Crypto;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;
use Cake\Datasource\EntityInterface;

/**
 * ContractsSubscribers Model
 *
 * @property \App\Model\Table\SubscribersTable&\Cake\ORM\Association\BelongsTo $Subscribers
 * @property \App\Model\Table\ContractsTable&\Cake\ORM\Association\BelongsTo $Contracts
 * @property \App\Model\Table\EndedReasonsTable&\Cake\ORM\Association\BelongsTo $EndedReasons
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $QCUsers
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\LogMailingsTable&\Cake\ORM\Association\HasMany $Mailings
 * @property \App\Model\Table\PaymentDebtsTable&\Cake\ORM\Association\HasMany $PaymentDebts
 *
 * @method \App\Model\Entity\ContractsSubscriber newEmptyEntity()
 * @method \App\Model\Entity\ContractsSubscriber newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ContractsSubscriber[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ContractsSubscriber get($primaryKey, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ContractsSubscriber[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ContractsSubscriber|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ContractsSubscriber[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ContractsSubscribersTable extends Table
{

    private $fields = [
        'is_suspicious',
        'ended_at',
        'canceled_at',
        'suspicious_created',
        'qc_comment',
        'comment'
    ];

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('contracts_subscribers');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('CounterCache', [
            'Sellers' => ['contract_count' => [
                'conditions' => ['signed_at IS NOT' => null, 'ended_at IS' => null]
            ]],
        ]);

        $this->belongsTo('Subscribers', [
            'foreignKey' => 'subscriber_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Contracts', [
            'foreignKey' => 'contract_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('EndedReasons', [
            'foreignKey' => 'ended_reason_id',
            'conditions' => ['EndedReasons.canceled' => 0],
            'joinType' => 'LEFT',
            //'className' =>  'EndedReasons'
        ]);
        $this->belongsTo('CanceledReasons', [
            'foreignKey' => 'cancel_reason_id',
            'conditions' => ['CanceledReasons.canceled' => 1],
            'joinType' => 'LEFT',
            'className' =>  'EndedReasons'
        ]);
        $this->belongsTo('Sellers', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'Users'
        ]);
        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
            'joinType' => 'LEFT',
            'className' => 'Users'
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'model_id',
            'conditions' => ['model' => 'ContractsSubscribers'],
            'className' =>  'Comments',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);
        $this->hasOne('Call2Comments', [
            'foreignKey' => 'model_id',
            'conditions' => ['model' => 'ContractsSubscribers', 'Call2Comments.type' => 'call-2'],
            'className' =>  'Comments',
            'strategy' => 'select'
        ]);
        $this->hasMany('Files', [
            'foreignKey' => 'contract_subscriber_id',
            'className' =>  'ContractSubscriberFiles',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->hasMany('FilesToSign', [
            'foreignKey' => 'contract_subscriber_id',
            'className' =>  'ContractSubscriberFiles',
            'conditions' => ['category IN' => ['mandat', 'conditions', 'contract'], 'signed' => 0],
        ]);
        $this->hasOne('ProofFile', [
            'foreignKey' => 'contract_subscriber_id',
            'className' =>  'ContractSubscriberFiles',
            'propertyName' => 'proofFile',
            'conditions' => ['ProofFile.category' => 'proof', 'protected' => 1],
        ]);
        $this->belongsTo('QCUser', [
            'foreignKey' => 'qc_user_id',
            'className' =>  'Users'
        ]);
        $this->hasMany('QualityFiles', [
            'foreignKey' => 'contract_subscriber_id',
            'className' =>  'ContractSubscriberFiles',
            'conditions' => ['category' => 'quality'],
        ]);
        $this->hasMany('NotAudioFiles', [
            'foreignKey' => 'contract_subscriber_id',
            'className' =>  'ContractSubscriberFiles',
            'conditions' => ['category NOT IN' => ['audio', 'audio-2', 'audio-3']],
        ]);
        $this->hasMany('AudioFiles', [
            'foreignKey' => 'contract_subscriber_id',
            'className' =>  'ContractSubscriberFiles',
            'conditions' => ['category IN' => ['audio', 'audio-2', 'audio-3']]
        ]);
        $this->hasMany('Mailings', [
            'foreignKey' => 'model_id',
            'conditions' => ['model' => 'ContractsSubscribers'],
            'className' =>  'LogMailings',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);

        $this->hasMany('Logs', [
            'foreignKey' => 'model_id',
            'conditions' => ['model' => 'ContractsSubscribers'],
            'className' =>  'LogUpdates',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);

        $this->hasMany('PaymentDebts', [
            'foreignKey' => 'contracts_subscriber_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('ContractsSubscribersUnpaids', [
            'foreignKey' => 'contracts_subscriber_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('RefundRequests', [
            'foreignKey' => 'contracts_subscriber_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        // $this->hasMany('Events', [
        //     'foreignKey' => 'model_id',
        //     'conditions' => ['model' => 'ContractsSubscribers'],
        //     'className' =>  'Events'
        // ]);

    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('subscriber_info')
            ->allowEmptyString('subscriber_info');

        $validator
            ->scalar('rum')
            ->maxLength('rum', 255)
            ->allowEmptyString('rum');

        $validator
            ->scalar('debit_iban')
            ->maxLength('debit_iban', 50)
            ->allowEmptyString('debit_iban')
            ->add('debit_iban', 'isValid', ['rule' => 'checkIBAN', 'message' => __('Le format de l\'IBAN est incorrect'), 'provider' => 'table']);
        
            $validator
            ->scalar('encrypted_iban')
            ->maxLength('encrypted_iban', 255)
            ->allowEmptyString('encrypted_iban');

        $validator
            ->scalar('debit_bic')
            ->maxLength('debit_bic', 50)
            ->allowEmptyString('debit_bic')
            ->add('debit_bic', 'isValid', ['rule' => 'checkBIC', 'message' => __('Le format du BIC est incorrect'), 'provider' => 'table']);

        $validator
            ->integer('debit_day')
            ->allowEmptyString('debit_day');

        // $validator
        //     ->scalar('type')
        //     ->maxLength('type', 50)
        //     ->notEmptyString('type');

        $validator
            ->dateTime('sended_at')
            ->allowEmptyDateTime('sended_at');

        $validator
            ->dateTime('received_at')
            ->allowEmptyDateTime('received_at');

        $validator
            ->dateTime('signed_at')
            ->allowEmptyDateTime('signed_at');

        $validator
            ->dateTime('exported_at')
            ->allowEmptyDateTime('exported_at');

        $validator
            ->dateTime('ended_at')
            ->allowEmptyDateTime('ended_at');


        return $validator;
    }

    public function validationWithoutIban($validator)
    {
        $validator
            ->scalar('debit_iban')
            ->maxLength('debit_iban', 50)
            ->allowEmptyString('debit_iban');

        $validator
            ->scalar('debit_bic')
            ->maxLength('debit_bic', 50)
            ->allowEmptyString('debit_bic');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('subscriber_id', 'Subscribers'), ['errorField' => 'subscriber_id']);
        $rules->add($rules->existsIn('contract_id', 'Contracts'), ['errorField' => 'contract_id']);
        $rules->add($rules->existsIn('user_id', 'Sellers'), ['errorField' => 'user_id']);
        $rules->addCreate($rules->isUnique(
            ['subscriber_id', 'contract_id', 'ended_at'],
            ['message' => 'Le client possède déjà un contrat identique !!']
        ));

        return $rules;
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        if (isset($data['debit_bic'])) {
            $data['debit_bic'] = strtoupper(str_replace(' ', '', trim($data['debit_bic'])));
        }
        if (isset($data['debit_iban'])) {
            $data['debit_iban'] = strtoupper(str_replace(' ', '', trim($data['debit_iban'])));
        }
    }

public function beforeSave(EventInterface $event, $entity, ArrayObject $options)
{
    if ($entity->isDirty('debit_iban')) {
        // Cas 1 : On vide l'IBAN
        if (empty($entity->debit_iban)) {
            $entity->encrypted_iban = null;
        } 
        // Cas 2 : On chiffre l'IBAN
        else {
            $key = Key::loadFromAsciiSafeString(Configure::read('Security.cryptoKey'));
            $entity->encrypted_iban = Crypto::encrypt($entity->debit_iban, $key);
            
            // IMPORTANT : On vide le champ en clair pour ne pas l'envoyer à la base
            $entity->debit_iban = null;
        }
    }
}

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {
            //dd($entity);
            $contractsTable = TableRegistry::getTableLocator()->get('Contracts');
            $contract = $contractsTable->get($entity->contract_id);

            //$entity->rum = 'CE-SE';
            $type = strtoupper(substr($contract->type, 0, 1));
            $entity->set('price', $contract->price);
            $entity->set('type', $contract->type);
            $entity->set('rum', Configure::read('App.codeRef') . $entity->id . '-' . rand(100, 999));
            if ($entity->debit_iban != null) {
                $key = Key::loadFromAsciiSafeString(Configure::read('Security.cryptoKey'));
                $entity->encrypted_iban = Crypto::encrypt($entity->debit_iban, $key);
            }
            $Table = TableRegistry::getTableLocator()->get('ContractsSubscribers');
            $Table->save($entity);

            return true;
        }

        if (!$entity->isNew()) {

            $modifyValues = $entity->extract($this->fields, true);    //New data for updated fields only

            // if(array_key_exists('auth_key', $modifyValues)){
            //     return true;
            // }

            if (count($modifyValues) == 0) {
                return true;    //If nothing has been updated
            }
            $user_id = (Router::getRequest() == null) ? 1 : Router::getRequest()->getSession()->read('Auth.id');
            //
            //$newValues = $entity->extract($this->fields, false);    //All new data
            //dd($modifyValues);

            $logTable = TableRegistry::getTableLocator()->get('LogUpdates');
            foreach ($modifyValues as $key => $value) {
                if (in_array($key, ['canceled_at', 'ended_at', 'suspicious_created']) and $value != null) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                if ($value != $entity->getOriginal($key)) {

                    $data = [
                        'user_id' => $user_id,
                        'model' => 'ContractsSubscribers',
                        'model_id' => $entity->id,
                        'field'    => $key,
                        'value_new' => $value,
                        'value_old' => $entity->getOriginal($key),
                    ];
                    $logTableEntity = $logTable->newEmptyEntity();
                    $logTableEntity = $logTable->patchEntity($logTableEntity, $data);
                    $logTable->save($logTableEntity);
                }
            }

            return true;
        }
    }

    public function findLast(Query $query, array $options)
    {
        $query = $query
            ->order(['LastContract.created' => 'DESC'])
            ->limit(1);

        return $query;
    }

    public function findValid(Query $query, array $options)
    {
        $query = $query
            ->where(['ValidContracts.signed_at IS NOT' => null, 'ValidContracts.ended_at IS' => null]);

        return $query;
    }

    public function findExportsExalog(Query $query, array $options)
    {
        $query = $query
            ->select(['exported_at'])
            ->group('exported_at')
            ->having(['exported_at IS NOT' => null]);

        return $query;
    }

    public function findWithoutProofFile(Query $query, array $options)
    {

        $query = $query
            ->where([
                'has_proof_file' => 0,
                'signed_at IS NOT' => null,
                'signature_provider_info IS NOT' => null,
                'signed_at <' => $options['delai'],
                'ended_at IS' => null
            ]);

        return $query;
    }

    public function findWithAudioFile(Query $query, array $options)
    {
        //$filter = ['category IN' => ['audio', 'audio-2']];
        $filter = ['category IN' => $options['cats']];
        $query = $query
            // ->innerJoinWith('AudioFiles')
            ->matching('Files', function (Query $q) use ($filter) {
                return $q->where($filter);
            })
            ->group('ContractsSubscribers.id');

        return $query;
    }

    public function findWithoutAudioFile(Query $query, array $options)
    {
        $filter = ['category IN' => $options['cats']];
        $query = $query
            ->notMatching('Files', function (Query $q) use ($filter) {
                return $q->where($filter);
            })
            ->group('ContractsSubscribers.id');

        return $query;
    }



    public function checkIBAN($iban)
    {
        if (strlen($iban) < 5) return false;
        $iban = strtolower(str_replace(' ', '', $iban));
        $Countries = array('al' => 28, 'ad' => 24, 'at' => 20, 'az' => 28, 'bh' => 22, 'be' => 16, 'ba' => 20, 'br' => 29, 'bg' => 22, 'cr' => 21, 'hr' => 21, 'cy' => 28, 'cz' => 24, 'dk' => 18, 'do' => 28, 'ee' => 20, 'fo' => 18, 'fi' => 18, 'fr' => 27, 'ge' => 22, 'de' => 22, 'gi' => 23, 'gr' => 27, 'gl' => 18, 'gt' => 28, 'hu' => 28, 'is' => 26, 'ie' => 22, 'il' => 23, 'it' => 27, 'jo' => 30, 'kz' => 20, 'kw' => 30, 'lv' => 21, 'lb' => 28, 'li' => 21, 'lt' => 20, 'lu' => 20, 'mk' => 19, 'mt' => 31, 'mr' => 27, 'mu' => 30, 'mc' => 27, 'md' => 24, 'me' => 22, 'nl' => 18, 'no' => 15, 'pk' => 24, 'ps' => 29, 'pl' => 28, 'pt' => 25, 'qa' => 29, 'ro' => 24, 'sm' => 27, 'sa' => 24, 'rs' => 22, 'sk' => 24, 'si' => 19, 'es' => 24, 'se' => 24, 'ch' => 21, 'tn' => 24, 'tr' => 26, 'ae' => 23, 'gb' => 22, 'vg' => 24);
        $Chars = array('a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16, 'h' => 17, 'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23, 'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29, 'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35);

        if (array_key_exists(substr($iban, 0, 2), $Countries) && strlen($iban) == $Countries[substr($iban, 0, 2)]) {

            $MovedChar = substr($iban, 4) . substr($iban, 0, 4);
            $MovedCharArray = str_split($MovedChar);
            $NewString = "";

            foreach ($MovedCharArray as $key => $value) {
                if (!is_numeric($MovedCharArray[$key])) {
                    if (!isset($Chars[$MovedCharArray[$key]])) return false;
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if (bcmod($NewString, '97') == 1) {
                return true;
            }
        }
        return false;

        // return true;
    }

    public function checkBic($swiftbic)
    {

        $regexp = '/^[A-Z]{6,6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3,3}){0,1}$/i';

        return (bool) preg_match($regexp, $swiftbic);
    }
}