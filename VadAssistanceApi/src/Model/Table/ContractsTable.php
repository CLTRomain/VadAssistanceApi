<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Contracts Model
 *
 * @property \App\Model\Table\ContractFilesTable&\Cake\ORM\Association\HasMany $ContractFiles
 * @property \App\Model\Table\SubscribersTable&\Cake\ORM\Association\BelongsToMany $Subscribers
 *
 * @method \App\Model\Entity\Contract newEmptyEntity()
 * @method \App\Model\Entity\Contract newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Contract> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Contract get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Contract findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Contract patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Contract> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Contract|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Contract saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Contract>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Contract>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Contract>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Contract> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Contract>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Contract>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Contract>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Contract> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ContractsTable extends Table
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

               parent::initialize($config);

        $this->setTable('contracts');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');




        $this->hasMany('ContractFiles', [
            'foreignKey' => 'contract_id'
        ]);

        $this->hasMany('ContractualFiles', [
            'foreignKey' => 'contract_id',
            'conditions' => ['ContractualFiles.category IN' => ['contract', 'mandat', 'conditions']],
            'className' => 'ContractFiles'
        ]);

        $this->hasOne('PaymentPlan', [
            'foreignKey' => 'contract_id',
            'conditions' => ['PaymentPlan.category' => 'payment_plan'],
            'className' => 'ContractFiles'
        ]);
        $this->hasMany('ContractsSubscribers', [
            'foreignKey' => 'contract_id',
        ]);

        $this->hasMany('Logs', [
            'foreignKey' => 'model_id',
            'conditions' => ['model' => 'contracts'],
            'className' =>  'LogUpdates'
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
            ->maxLength('uuid', 255)
            ->allowEmptyString('uuid');

        $validator
            ->scalar('name')
            ->maxLength('name', 191)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 100)
            ->allowEmptyString('slug');

        $validator
            ->scalar('type')
            ->maxLength('type', 191)
            ->notEmptyString('type');

        $validator
            ->boolean('need_address')
            ->notEmptyString('need_address');

        $validator
            ->decimal('price')
            ->allowEmptyString('price');

        $validator
            ->integer('position')
            ->allowEmptyString('position');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('resume')
            ->allowEmptyString('resume');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        return $validator;
    }
}
