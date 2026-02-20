<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Demands Model
 *
 * @method \App\Model\Entity\Demand newEmptyEntity()
 * @method \App\Model\Entity\Demand newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Demand> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Demand get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Demand findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Demand patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Demand> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Demand|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Demand saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Demand>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Demand>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Demand>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Demand> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Demand>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Demand>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Demand>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Demand> deleteManyOrFail(iterable $entities, array $options = [])
 */
class DemandsTable extends Table
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

        $this->setTable('demands');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->scalar('type')
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 255)
            ->allowEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 255)
            ->allowEmptyString('last_name');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('phone')
            ->allowEmptyString('phone');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->integer('status')
            ->notEmptyString('status');

        $validator
            ->scalar('skill')
            ->allowEmptyString('skill');

        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        $validator
            ->boolean('rgpd_consent')
            ->allowEmptyString('rgpd_consent');

        $validator
            ->scalar('company_name')
            ->allowEmptyString('company_name');

        return $validator;
    }
}
