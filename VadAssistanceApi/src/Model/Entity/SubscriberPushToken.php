<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class SubscriberPushToken extends Entity
{
    protected array $_accessible = [
        'subscriber_id' => true,
        'push_token'    => true,
        'created_at'    => true,
    ];
}
