<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ContractsSubscribersController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ContractsSubscribersController Test Case
 *
 * @link \App\Controller\ContractsSubscribersController
 */
class ContractsSubscribersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ContractsSubscribers',
        'app.Subscribers',
        'app.Contracts',
        'app.EndedReasons',
        'app.CanceledReasons',
        'app.Sellers',
        'app.Admins',
        'app.Comments',
        'app.Call2Comments',
        'app.Files',
        'app.FilesToSign',
        'app.ProofFile',
        'app.QCUser',
        'app.QualityFiles',
        'app.NotAudioFiles',
        'app.AudioFiles',
        'app.Mailings',
        'app.Logs',
        'app.PaymentDebts',
        'app.ContractsSubscribersUnpaids',
        'app.RefundRequests',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\ContractsSubscribersController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\ContractsSubscribersController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\ContractsSubscribersController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\ContractsSubscribersController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\ContractsSubscribersController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
