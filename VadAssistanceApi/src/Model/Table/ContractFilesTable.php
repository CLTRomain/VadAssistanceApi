<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ContractFiles Model
 *
 * @property \App\Model\Table\ContractsTable&\Cake\ORM\Association\BelongsTo $Contracts
 *
 * @method \App\Model\Entity\ContractFile newEmptyEntity()
 * @method \App\Model\Entity\ContractFile newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ContractFile> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ContractFile get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ContractFile findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ContractFile patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ContractFile> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ContractFile|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ContractFile saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ContractFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractFile>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContractFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractFile> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContractFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractFile>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContractFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContractFile> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ContractFilesTable extends Table
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

        $this->setTable('contract_files');
        $this->setDisplayField('name');

        $this->addBehavior('Timestamp');

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
            ->nonNegativeInteger('id')
            ->requirePresence('id', 'create')
            ->notEmptyString('id');

        $validator
            ->integer('contract_id')
            ->notEmptyString('contract_id');

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
            ->integer('position')
            ->allowEmptyString('position');

        $validator
            ->boolean('should_be_autocompleted')
            ->notEmptyString('should_be_autocompleted');

        $validator
            ->scalar('category')
            ->maxLength('category', 255)
            ->notEmptyString('category');

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
        $rules->add($rules->existsIn(['contract_id'], 'Contracts'), ['errorField' => 'contract_id']);

        return $rules;
    }
}
