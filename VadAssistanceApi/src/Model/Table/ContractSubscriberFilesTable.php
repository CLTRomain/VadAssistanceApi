<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ContractSubscriberFiles Model
 *
 * @method \App\Model\Entity\ContractSubscriberFile newEmptyEntity()
 * @method \App\Model\Entity\ContractSubscriberFile newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ContractSubscriberFile> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ContractSubscriberFile get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ContractSubscriberFile findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ContractSubscriberFile patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ContractSubscriberFile> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ContractSubscriberFile|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ContractSubscriberFile saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ContractSubscriberFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractSubscriberFile>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContractSubscriberFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractSubscriberFile> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContractSubscriberFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractSubscriberFile>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContractSubscriberFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractSubscriberFile> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ContractSubscriberFilesTable extends Table
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

        $this->setTable('contract_subscriber_files');
        $this->setDisplayField('name');
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
            ->nonNegativeInteger('contract_subscriber_id')
            ->requirePresence('contract_subscriber_id', 'create')
            ->notEmptyString('contract_subscriber_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->integer('size')
            ->allowEmptyString('size');

        $validator
            ->scalar('type')
            ->maxLength('type', 100)
            ->allowEmptyString('type');

        $validator
            ->scalar('ext')
            ->maxLength('ext', 5)
            ->allowEmptyString('ext');

        $validator
            ->scalar('url')
            ->maxLength('url', 255)
            ->allowEmptyString('url');

        $validator
            ->scalar('category')
            ->maxLength('category', 25)
            ->notEmptyString('category');

        $validator
            ->integer('position')
            ->notEmptyString('position');

        $validator
            ->boolean('signed')
            ->notEmptyString('signed');

        $validator
            ->boolean('protected')
            ->notEmptyString('protected');

        $validator
            ->integer('year')
            ->allowEmptyString('year');

        $validator
            ->integer('month')
            ->allowEmptyString('month');

        return $validator;
    }
}
