<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * LogMailings Model
 *
 * @method \App\Model\Entity\LogMailing newEmptyEntity()
 * @method \App\Model\Entity\LogMailing newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\LogMailing> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\LogMailing get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\LogMailing findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\LogMailing patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\LogMailing> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\LogMailing|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\LogMailing saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\LogMailing>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\LogMailing>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LogMailing>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\LogMailing> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LogMailing>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\LogMailing>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\LogMailing>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\LogMailing> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LogMailingsTable extends Table
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

        $this->setTable('log_mailings');
        $this->setDisplayField('model');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('model')
            ->maxLength('model', 50)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->nonNegativeInteger('model_id')
            ->requirePresence('model_id', 'create')
            ->notEmptyString('model_id');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 100)
            ->allowEmptyString('subject');

        $validator
            ->scalar('recipient')
            ->maxLength('recipient', 100)
            ->allowEmptyString('recipient');

        $validator
            ->scalar('content')
            ->maxLength('content', 4294967295)
            ->allowEmptyString('content');

        $validator
            ->scalar('uuid')
            ->maxLength('uuid', 50)
            ->allowEmptyString('uuid');

        $validator
            ->scalar('file_name')
            ->maxLength('file_name', 255)
            ->allowEmptyString('file_name');

        $validator
            ->scalar('event')
            ->maxLength('event', 50)
            ->allowEmptyString('event');

        $validator
            ->dateTime('event_date')
            ->allowEmptyDateTime('event_date');

        $validator
            ->dateTime('failed')
            ->allowEmptyDateTime('failed');

        $validator
            ->dateTime('delivered')
            ->allowEmptyDateTime('delivered');

        $validator
            ->dateTime('opened')
            ->allowEmptyDateTime('opened');

        return $validator;
    }
}
