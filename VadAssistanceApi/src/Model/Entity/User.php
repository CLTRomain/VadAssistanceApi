<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $user_ref
 * @property string|null $username
 * @property string $email
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string $password
 * @property string|null $lost_password
 * @property string|null $tfa_secret
 * @property string|null $api_key
 * @property int $role_id
 * @property int|null $parent_id
 * @property string|null $company
 * @property int|null $callcenter_id
 * @property int|null $artisan_id
 * @property string|null $telephone
 * @property int|null $contract_count
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $admin_id
 * @property string|null $role_old
 *
 * @property \App\Model\Entity\ParentUser $parent_user
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\ContractsSubscriber[] $contracts_subscribers
 * @property \App\Model\Entity\Subscriber[] $subscribers
 * @property \App\Model\Entity\SupportRequest[] $support_requests
 * @property \App\Model\Entity\ChildUser[] $child_users
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_ref' => true,
        'username' => true,
        'email' => true,
        'last_name' => true,
        'first_name' => true,
        'password' => true,
        'lost_password' => true,
        'tfa_secret' => true,
        'api_key' => true,
        'role_id' => true,
        'parent_id' => true,
        'company' => true,
        'callcenter_id' => true,
        'artisan_id' => true,
        'telephone' => true,
        'contract_count' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'admin_id' => true,
        'role_old' => true,
        'parent_user' => true,
        'comments' => true,
        'contracts_subscribers' => true,
        'subscribers' => true,
        'support_requests' => true,
        'child_users' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
    ];
}
