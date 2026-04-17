<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class SubscriberPushTokensTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('subscriber_push_tokens');
        $this->setPrimaryKey('id');

        $this->belongsTo('Subscribers', [
            'foreignKey' => 'subscriber_id',
        ]);
    }
}
