<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Contract Entity
 *
 * @property int $id
 * @property string|null $uuid
 * @property string $name
 * @property string|null $slug
 * @property string $type
 * @property bool $need_address
 * @property string|null $price
 * @property int|null $position
 * @property string|null $description
 * @property string|null $resume
 * @property bool $is_active
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\ContractFile[] $contract_files
 * @property \App\Model\Entity\Subscriber[] $subscribers
 */
class Contract extends Entity
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
        'uuid' => true,
        'name' => true,
        'slug' => true,
        'type' => true,
        'need_address' => true,
        'price' => true,
        'position' => true,
        'description' => true,
        'resume' => true,
        'is_active' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'contract_files' => true,
        'subscribers' => true,
    ];
}
