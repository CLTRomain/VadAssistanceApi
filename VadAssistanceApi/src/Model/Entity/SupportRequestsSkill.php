<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SupportRequestsSkill Entity
 *
 * @property int $id
 * @property int $support_request_id
 * @property int $skill_id
 * @property string|null $to_do
 * @property float|null $payment_amount
 * @property float|null $tax
 * @property \Cake\I18n\Date|null $payment_date
 * @property string|null $payment_dest
 * @property string|null $check_number
 *
 * @property \App\Model\Entity\SupportRequest $support_request
 */
class SupportRequestsSkill extends Entity
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
        'support_request_id' => true,
        'skill_id' => true,
        'to_do' => true,
        'payment_amount' => true,
        'tax' => true,
        'payment_date' => true,
        'payment_dest' => true,
        'check_number' => true,
        'support_request' => true,
    ];
}
