<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SupportRequestsSkills Model
 *
 * @property \App\Model\Table\SupportRequestsTable&\Cake\ORM\Association\BelongsTo $SupportRequests
 *
 * @method \App\Model\Entity\SupportRequestsSkill newEmptyEntity()
 * @method \App\Model\Entity\SupportRequestsSkill newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SupportRequestsSkill> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SupportRequestsSkill get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SupportRequestsSkill findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SupportRequestsSkill patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SupportRequestsSkill> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SupportRequestsSkill|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SupportRequestsSkill saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestsSkill>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestsSkill>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestsSkill>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestsSkill> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestsSkill>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestsSkill>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequestsSkill>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequestsSkill> deleteManyOrFail(iterable $entities, array $options = [])
 */
class SupportRequestsSkillsTable extends Table
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

        $this->setTable('support_requests_skills');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('SupportRequests', [
            'foreignKey' => 'support_request_id',
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
            ->nonNegativeInteger('support_request_id')
            ->notEmptyString('support_request_id');

        $validator
            ->nonNegativeInteger('skill_id')
            ->requirePresence('skill_id', 'create')
            ->notEmptyString('skill_id');

        $validator
            ->scalar('to_do')
            ->allowEmptyString('to_do');

        $validator
            ->numeric('payment_amount')
            ->allowEmptyString('payment_amount');

        $validator
            ->numeric('tax')
            ->allowEmptyString('tax');

        $validator
            ->date('payment_date')
            ->allowEmptyDate('payment_date');

        $validator
            ->scalar('payment_dest')
            ->maxLength('payment_dest', 15)
            ->allowEmptyString('payment_dest');

        $validator
            ->scalar('check_number')
            ->maxLength('check_number', 20)
            ->allowEmptyString('check_number');

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
        $rules->add($rules->existsIn(['support_request_id'], 'SupportRequests'), ['errorField' => 'support_request_id']);

        return $rules;
    }
}
