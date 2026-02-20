<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_ref' => 'Lorem ip',
                'username' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'last_name' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'lost_password' => 'Lorem ipsum dolor sit amet',
                'tfa_secret' => 'Lorem ipsum dolor sit amet',
                'api_key' => 'Lorem ipsum dolor sit amet',
                'role_id' => 1,
                'parent_id' => 1,
                'company' => 'Lorem ipsum dolor sit amet',
                'callcenter_id' => 1,
                'artisan_id' => 1,
                'telephone' => 'Lorem ipsum dolor ',
                'contract_count' => 1,
                'deleted' => 1771582044,
                'created' => '2026-02-20 10:07:24',
                'modified' => '2026-02-20 10:07:24',
                'admin_id' => 1,
                'role_old' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
