<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use ArrayObject;

/**
 * Subscribers Model
 *
 * @property \App\Model\Table\ContractsTable&\Cake\ORM\Association\BelongsToMany $Contracts
 *
 * @method \App\Model\Entity\Subscriber newEmptyEntity()
 * @method \App\Model\Entity\Subscriber newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Subscriber> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Subscriber get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Subscriber findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Subscriber patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Subscriber> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Subscriber|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Subscriber saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Subscriber>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscriber>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Subscriber>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscriber> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Subscriber>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscriber>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Subscriber>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscriber> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SubscribersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('subscribers');
        $this->setDisplayField('country');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Contracts', [
            'foreignKey' => 'subscriber_id',
            'targetForeignKey' => 'contract_id',
            'joinTable' => 'contracts_subscribers',
        ]);
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
            ->scalar('uuid')
            ->maxLength('uuid', 50)
            ->allowEmptyString('uuid');

        $validator
            ->scalar('customer_number')
            ->maxLength('customer_number', 13)
            ->allowEmptyString('customer_number');

        $validator
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('admin_id')
            ->allowEmptyString('admin_id');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->boolean('email_validated')
            ->notEmptyString('email_validated');

        $validator
                ->scalar('hash_mail')
        ->maxLength('hash_mail', 30)
        ->allowEmptyString('hash_mail');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        $validator
            ->scalar('lost_password')
            ->maxLength('lost_password', 255)
            ->allowEmptyString('lost_password');

        $validator
            ->dateTime('lost_password_validity')
            ->allowEmptyDateTime('lost_password_validity');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 20)
            ->allowEmptyString('phone');

        $validator
            ->scalar('civility')
            ->maxLength('civility', 10)
            ->allowEmptyString('civility');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 100)
            ->allowEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 100)
            ->allowEmptyString('last_name');

        $validator
            ->date('birth_date')
            ->allowEmptyDate('birth_date');

        $validator
            ->scalar('birth_place')
            ->maxLength('birth_place', 255)
            ->allowEmptyString('birth_place');

        $validator
            ->scalar('comment')
            ->allowEmptyString('comment');

        $validator
            ->scalar('company')
            ->maxLength('company', 100)
            ->allowEmptyString('company');

        $validator
            ->scalar('address')
            ->maxLength('address', 255)
            ->allowEmptyString('address');

        $validator
            ->scalar('address_rest')
            ->maxLength('address_rest', 255)
            ->allowEmptyString('address_rest');

        $validator
            ->scalar('postal_code')
            ->maxLength('postal_code', 191)
            ->allowEmptyString('postal_code');

        $validator
            ->scalar('city')
            ->maxLength('city', 255)
            ->allowEmptyString('city');

        $validator
            ->scalar('country')
            ->maxLength('country', 3)
            ->notEmptyString('country');

        $validator
            ->dateTime('validated_account')
            ->allowEmptyDateTime('validated_account');

        $validator
            ->dateTime('activated_account')
            ->allowEmptyDateTime('activated_account');

        $validator
            ->dateTime('privacy_policy_acceptance')
            ->allowEmptyDateTime('privacy_policy_acceptance');

        $validator
            ->dateTime('partner_offers_acceptance')
            ->allowEmptyDateTime('partner_offers_acceptance');

        return $validator;
    }


public function afterSave(\Cake\Event\EventInterface $event, \Cake\Datasource\EntityInterface $entity, \ArrayObject $options)
{
    // On n'agit que si c'est une nouvelle inscription ou si le numéro client est vide
    if ($entity->isNew() || empty($entity->customer_number)) {

        $ls = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        
        // On s'assure d'avoir la date de création
        $created = $entity->created ?? new \DateTime();
        
        $date = $created->format('md'); 
        $customer_number = $created->format('y'); 
        
        $splitedDate = str_split($date);
        foreach ($splitedDate as $value) {
            $customer_number .= $ls[(int)$value];
        }
        
        $customer_number .= str_pad((string)$entity->id, 7, '0', STR_PAD_LEFT);

        // On met à jour l'entité
        $entity->set('customer_number', $customer_number);

        // SOLUTION POUR ÉVITER LA BOUCLE INFINIE :
        // On sauvegarde l'entité en désactivant les événements pour cette opération précise
        $this->save($entity, ['checkRules' => false, 'atomic' => false, 'events' => false]);

        return true;
    }
}


}
