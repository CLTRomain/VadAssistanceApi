<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Demand Entity
 *
 * @property int $id
 * @property string $type
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $description
 * @property int $status
 * @property string|null $skill
 * @property \Cake\I18n\DateTime|null $created_at
 * @property bool|null $rgpd_consent
 * @property string|null $company_name
 */
class Demand extends Entity
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
        'type' => true,
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'phone' => true,
        'description' => true,
        'status' => true,
        'skill' => true,
        'created_at' => true,
        'rgpd_consent' => true,
        'company_name' => true,
    ];
}
