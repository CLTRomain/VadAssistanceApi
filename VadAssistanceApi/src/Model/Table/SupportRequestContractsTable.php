<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use ArrayObject;
use Cake\ORM\Table;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;
use Cake\ORM\Query\SelectQuery;
use Cake\Datasource\EntityInterface;

/**
 * SupportRequestContacts Model
@@ -118,4 +123,24 @@ public function buildRules(RulesChecker $rules): RulesChecker

        return $rules;
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {

            $Notifications = TableRegistry::getTableLocator()->get('Notifications');
            $Users = TableRegistry::getTableLocator()->get('Users');
            $admins = $Users->find()->where(['role_id' => 2, 'deleted IS' => null])->select(['id'])->all();
                foreach ($admins as $key => $user) {
                    $notification = $Notifications->newEntity([
                        'user_id' => $user->id,
                        'title' => 'Nouvelle demande d\'intervention',
                        'body' => 'Un clent a fait une demande d\'intervention en ligne.',
                        'url' => '/admin/support-request-contact/view/' . $entity->id 
                    ]);
                    $Notifications->save($notification);
                }

        }
    }
}