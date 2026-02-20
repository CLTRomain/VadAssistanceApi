<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SupportRequestContact Entity
 *
 * @property int $id
 * @property int $subscriber_id
 * @property int $contract_id
 * @property string $subject
 * @property string $text
 * @property string $status
 * @property bool $hide
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $modified_at
 *
 * @property \App\Model\Entity\Subscriber $subscriber
 * @property \App\Model\Entity\Contract $contract
 */
class SupportRequestContact extends Entity
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
        'subscriber_id' => true,
        'contract_id' => true,
        'subject' => true,
        'text' => true,
        'status' => true,
        'hide' => true,
        'created_at' => true,
        'modified_at' => true,
        'subscriber' => true,
        'contract' => true,
    ];
}
