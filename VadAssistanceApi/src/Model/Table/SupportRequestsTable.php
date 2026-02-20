<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SupportRequests Model
 *
 * @property \App\Model\Table\SupportRequestFilesTable&\Cake\ORM\Association\HasMany $SupportRequestFiles
 * @property \App\Model\Table\SupportRequestsSkillsTable&\Cake\ORM\Association\HasMany $SupportRequestsSkills
 *
 * @method \App\Model\Entity\SupportRequest newEmptyEntity()
 * @method \App\Model\Entity\SupportRequest newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SupportRequest> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SupportRequest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SupportRequest findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SupportRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SupportRequest> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SupportRequest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SupportRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequest>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequest> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequest>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SupportRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SupportRequest> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SupportRequestsTable extends Table
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

        $this->setTable('support_requests');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('SupportRequestFiles', [
            'foreignKey' => 'support_request_id',
        ]);
        $this->hasMany('SupportRequestsSkills', [
            'foreignKey' => 'support_request_id',
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
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('structure')
            ->maxLength('structure', 20)
            ->allowEmptyString('structure');

        $validator
            ->integer('contract_subscriber_id')
            ->requirePresence('contract_subscriber_id', 'create')
            ->notEmptyString('contract_subscriber_id');

        $validator
            ->nonNegativeInteger('artisan_id')
            ->allowEmptyString('artisan_id');

        $validator
            ->date('request_date')
            ->allowEmptyDate('request_date');

        $validator
            ->boolean('anteriority')
            ->notEmptyString('anteriority');

        $validator
            ->notEmptyString('in_progress');

        return $validator;
    }
}
