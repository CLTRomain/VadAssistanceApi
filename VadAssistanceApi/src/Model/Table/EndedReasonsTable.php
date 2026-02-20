<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EndedReasons Model
 *
 * @property \App\Model\Table\ContractsSubscribersTable&\Cake\ORM\Association\HasMany $ContractsSubscribers
 *
 * @method \App\Model\Entity\EndedReason newEmptyEntity()
 * @method \App\Model\Entity\EndedReason newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EndedReason> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EndedReason get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EndedReason findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EndedReason patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EndedReason> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EndedReason|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EndedReason saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EndedReason>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EndedReason>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EndedReason>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EndedReason> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EndedReason>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EndedReason>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EndedReason>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EndedReason> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EndedReasonsTable extends Table
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

        $this->setTable('ended_reasons');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('ContractsSubscribers', [
            'foreignKey' => 'ended_reason_id',
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
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->boolean('canceled')
            ->notEmptyString('canceled');

        return $validator;
    }
}
