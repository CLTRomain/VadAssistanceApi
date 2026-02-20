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
 * Users Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $ParentUsers
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\ContractsSubscribersTable&\Cake\ORM\Association\HasMany $ContractsSubscribers
 * @property \App\Model\Table\SubscribersTable&\Cake\ORM\Association\HasMany $Subscribers
 * @property \App\Model\Table\SupportRequestsTable&\Cake\ORM\Association\HasMany $SupportRequests
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $ChildUsers
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('full_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'Roles' => ['user_count'],
        ]);

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Parents', [
            'className' => 'Users',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('LogUpdates', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('LogUsers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Subscribers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ChildUsers', [
            'className' => 'Users',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ActiveSellers', [
            'className' => 'Users',
            'foreignKey' => 'parent_id',
            'conditions' => ['ActiveSellers.deleted IS' => null]
        ]);
        $this->hasMany('AllSellers', [
            'className' => 'Users',
            'foreignKey' => 'parent_id',
        ]);
        $this->belongsToMany('Modules', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'module_id',
            'joinTable' => 'modules_users',
        ]);
        $this->hasMany('ContractsSubscribers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasOne('Partnerships', [
            'foreignKey' => 'user_id',
            'conditions' => ['ended_at IS' => null]
        ]);
        $this->hasMany('EndedPartnerships', [
            'foreignKey' => 'user_id',
            'conditions' => ['ended_at IS NOT' => null]
        ]);
        $this->hasMany('Files', [
            'foreignKey' => 'user_id',
            'className' => 'UserFiles'
        ]);
        $this->hasMany('ArtisanFiles', [
            'foreignKey' => 'user_id',
            'className' => 'UserFiles',
            'conditions' => ['for_artisan' => 1]
        ]);
        $this->hasMany('Webpushes', [
            'foreignKey' => 'user_id',
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
            ->scalar('user_ref')
            ->maxLength('user_ref', 10)
            ->allowEmptyString('user_ref');

        $validator
            ->scalar('username')
            ->maxLength('username', 50)
            ->allowEmptyString('username');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 50)
            ->allowEmptyString('last_name');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 50)
            ->allowEmptyString('first_name');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('lost_password')
            ->maxLength('lost_password', 255)
            ->allowEmptyString('lost_password');

        $validator
            ->scalar('tfa_secret')
            ->maxLength('tfa_secret', 255)
            ->allowEmptyString('tfa_secret');

        $validator
            ->scalar('api_key')
            ->maxLength('api_key', 50)
            ->allowEmptyString('api_key');

        $validator
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->nonNegativeInteger('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->scalar('company')
            ->maxLength('company', 50)
            ->allowEmptyString('company');

        $validator
            ->integer('callcenter_id')
            ->allowEmptyString('callcenter_id');

        $validator
            ->integer('artisan_id')
            ->allowEmptyString('artisan_id');

        $validator
            ->scalar('telephone')
            ->maxLength('telephone', 20)
            ->allowEmptyString('telephone');

        $validator
            ->integer('contract_count')
            ->allowEmptyString('contract_count');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->integer('admin_id')
            ->allowEmptyString('admin_id');

        $validator
            ->scalar('role_old')
            ->maxLength('role_old', 191)
            ->allowEmptyString('role_old');


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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->existsIn(['parent_id'], 'ParentUsers'), ['errorField' => 'parent_id']);

        return $rules;
    }
}
