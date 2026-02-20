<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LogMailing Entity
 *
 * @property int $id
 * @property string $model
 * @property int $model_id
 * @property string|null $subject
 * @property string|null $recipient
 * @property string|null $content
 * @property string|null $uuid
 * @property string|null $file_name
 * @property string|null $event
 * @property \Cake\I18n\DateTime|null $event_date
 * @property \Cake\I18n\DateTime|null $failed
 * @property \Cake\I18n\DateTime|null $delivered
 * @property \Cake\I18n\DateTime|null $opened
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class LogMailing extends Entity
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
        'model' => true,
        'model_id' => true,
        'subject' => true,
        'recipient' => true,
        'content' => true,
        'uuid' => true,
        'file_name' => true,
        'event' => true,
        'event_date' => true,
        'failed' => true,
        'delivered' => true,
        'opened' => true,
        'created' => true,
        'modified' => true,
    ];
}
