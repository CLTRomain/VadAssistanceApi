<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ContractSubscriberFile Entity
 *
 * @property int $id
 * @property int $contract_subscriber_id
 * @property string|null $name
 * @property int|null $size
 * @property string|null $type
 * @property string|null $ext
 * @property string|null $url
 * @property string $category
 * @property int $position
 * @property bool $signed
 * @property bool $protected
 * @property int|null $year
 * @property int|null $month
 * @property \Cake\I18n\DateTime|null $created
 */
class ContractSubscriberFile extends Entity
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
        'contract_subscriber_id' => true,
        'name' => true,
        'size' => true,
        'type' => true,
        'ext' => true,
        'url' => true,
        'category' => true,
        'position' => true,
        'signed' => true,
        'protected' => true,
        'year' => true,
        'month' => true,
        'created' => true,
    ];

protected array $_virtual = ['full_name', 'version'];


    protected function _getFullName()
    {
        return $this->name . '.' . $this->ext;
    }

    protected function _getVersion()
    {
        if($this->category == 'payment_plan' AND $this->year != null)
        return $this->year . '-' . ($this->year + 1);
        else
        return null;
    }
}
