<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ContractFile Entity
 *
 * @property int $id
 * @property int $contract_id
 * @property string|null $name
 * @property int|null $size
 * @property string|null $type
 * @property string|null $ext
 * @property string|null $url
 * @property int|null $position
 * @property bool $should_be_autocompleted
 * @property string $category
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Contract $contract
 */
class ContractFile extends Entity
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
        'id' => true,
        'contract_id' => true,
        'name' => true,
        'size' => true,
        'type' => true,
        'ext' => true,
        'url' => true,
        'position' => true,
        'should_be_autocompleted' => true,
        'category' => true,
        'created' => true,
        'modified' => true,
        'contract' => true,
    ];

        protected array $_virtual = ['full_name'];


    protected function _getFullName()
    {
        return $this->name . '.' . $this->ext;
    }
}
