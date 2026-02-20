<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SupportRequest Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $structure
 * @property int $contract_subscriber_id
 * @property int|null $artisan_id
 * @property \Cake\I18n\Date|null $request_date
 * @property bool $anteriority
 * @property int $in_progress
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\SupportRequestFile[] $support_request_files
 * @property \App\Model\Entity\SupportRequestsSkill[] $support_requests_skills
 */
class SupportRequest extends Entity
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
        'user_id' => true,
        'structure' => true,
        'contract_subscriber_id' => true,
        'artisan_id' => true,
        'request_date' => true,
        'anteriority' => true,
        'in_progress' => true,
        'created' => true,
        'modified' => true,
        'support_request_files' => true,
        'support_requests_skills' => true,
    ];
}
