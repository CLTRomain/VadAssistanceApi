<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SupportRequestContacts Model
 *
 * @property \App\Model\Table\SubscribersTable&\Cake\ORM\Association\BelongsTo $Subscribers
 * @property \App\Model\Table\ContractsTable&\Cake\ORM\Association\BelongsTo $Contracts
 *
 * @method \App\Model\Entity\SupportRequestContact newEmptyEntity()
 * @method \App\Model\Entity\SupportRequestContact newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SupportRequestContact> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SupportRequestContact get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SupportRequestContact findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SupportRequestContact patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SupportRequestContact> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SupportRequestContact|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SupportRequestContact saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestContact>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestContact>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestContact>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestContact> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestContact>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestContact>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestContact>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestContact> deleteManyOrFail(iterable $entities, array $options = [])
 */
class SupportRequestContactsTable extends Table
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

        $this->setTable('support_request_contacts');
        $this->setDisplayField('subject');
        $this->setPrimaryKey('id');

        $this->belongsTo('Subscribers', [
            'foreignKey' => 'subscriber_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Contracts', [
            'foreignKey' => 'contract_id',
            'joinType' => 'INNER',
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
            ->integer('subscriber_id')
            ->notEmptyString('subscriber_id');

        $validator
            ->integer('contract_id')
            ->notEmptyString('contract_id');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 20)
            ->requirePresence('subject', 'create')
            ->notEmptyString('subject');

        $validator
            ->scalar('text')
            ->requirePresence('text', 'create')
            ->notEmptyString('text');

        $validator
            ->scalar('status')
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        $validator
            ->boolean('hide')
            ->requirePresence('hide', 'create')
            ->notEmptyString('hide');

        $validator
            ->dateTime('created_at')
            ->requirePresence('created_at', 'create')
            ->notEmptyDateTime('created_at');

        $validator
            ->dateTime('modified_at')
            ->requirePresence('modified_at', 'create')
            ->notEmptyDateTime('modified_at');

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
        $rules->add($rules->existsIn(['subscriber_id'], 'Subscribers'), ['errorField' => 'subscriber_id']);
        $rules->add($rules->existsIn(['contract_id'], 'Contracts'), ['errorField' => 'contract_id']);

        return $rules;
    }
}
