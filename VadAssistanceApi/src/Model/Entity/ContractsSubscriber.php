<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Defuse\Crypto\Key;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use DateTime;
use Defuse\Crypto\Crypto;

/**
 * ContractsSubscriber Entity
 *
 * @property int $id
 * @property int $subscriber_id
 * @property string|null $subscriber_info
 * @property int $contract_id
 * @property int|null $user_id
 * @property int|null $admin_id
 * @property string|null $rum
 * @property string|null $debit_iban
 * @property string|null $encrypted_iban
 * @property string|null $debit_bic
 * @property int|null $debit_day
 * @property string $debit_recurrence
 * @property string $type
 * @property float|null $price
 * @property string|null $_ended_reason
 * @property \Cake\I18n\DateTime|null $sended_at
 * @property \Cake\I18n\DateTime|null $received_at
 * @property \Cake\I18n\DateTime|null $signed_at
 * @property \Cake\I18n\DateTime|null $ended_at
 * @property \Cake\I18n\DateTime|null $canceled_at
 * @property int|null $cancel_reason_id
 * @property int|null $ended_reason_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $exported_at
 * @property string|null $comment
 * @property string|null $signature_provider
 * @property string|null $signature_provider_info
 * @property bool $has_proof_file
 * @property int|null $qc_user_id
 * @property string|null $qc_comment
 * @property int $is_suspicious
 * @property \Cake\I18n\DateTime|null $suspicious_created
 * @property bool $precontractuels_sended
 * @property bool $is_migrate
 * @property string|null $docusign_envelop_type
 * @property string|null $docusign_envelop_code
 * @property string|null $docusign_envelop_url
 * @property string|null $docusign_envelop_token
 * @property string|null $docusign_envelop_id
 * @property int|null $_distributor_id
 * @property int|null $_admin_id
 * @property string|null $_placeofbirth
 * @property string|null $_birthday
 * @property string $_country
 * @property string|null $_city
 * @property string|null $_postalcode
 * @property string|null $_address_rest
 * @property string|null $_address
 * @property string|null $_company
 * @property string|null $_lastname
 * @property string|null $_firstname
 * @property string|null $_civility
 * @property string|null $_phone
 * @property string|null $_email
 *
 * @property \App\Model\Entity\Subscriber $subscriber
 * @property \App\Model\Entity\Contract $contract
 * @property \App\Model\Entity\EndedReason $ended_reason
 */
class ContractsSubscriber extends Entity
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
        'subscriber_id' => true,
        'subscriber_info' => true,
        'contract_id' => true,
        'user_id' => true,
        'admin_id' => true,
        'rum' => true,
        'debit_iban' => true,
        'encrypted_iban' => true,
        'debit_bic' => true,
        'debit_day' => true,
        'debit_recurrence' => true,
        'type' => true,
        'price' => true,
        '_ended_reason' => true,
        'sended_at' => true,
        'received_at' => true,
        'signed_at' => true,
        'ended_at' => true,
        'canceled_at' => true,
        'cancel_reason_id' => true,
        'ended_reason_id' => true,
        'created' => true,
        'modified' => true,
        'exported_at' => true,
        'comment' => true,
        'signature_provider' => true,
        'signature_provider_info' => true,
        'has_proof_file' => true,
        'qc_user_id' => true,
        'qc_comment' => true,
        'is_suspicious' => true,
        'suspicious_created' => true,
        'precontractuels_sended' => true,
        'is_migrate' => true,
        'subscriber' => true,
        'contract' => true,
        'ended_reason' => true,
    ];


    protected function _setDebitBic($value)
    {
        if ($value != null) {
            return strtoupper($value);
        }
        return $value;
    }

    //protected $_virtual = ['ref', 'folder_path', 'signature_info', 'renewal_date', 'payment_plan_sending_date', 'decrypted_iban'];


    protected function _getRef()
    {
        $ref = '';
        if ($this->type != null) {
            //$ref = strtoupper(substr($this->type, 0, 1)) . $this->id;
            $ref = Configure::read('App.codeRef') . $this->id;
        }
        return $ref;
    }

    protected function _getFolderPath()
    {
        return SUBSCRIBERS_PATH . $this->subscriber_id . DS . $this->id . DS;
    }

    protected function _getSignatureInfo()
    {
        $res = [];
        if ($this->signature_provider_info != null) {
            $res = json_decode($this->signature_provider_info);
        }
        return $res;
    }

    protected function _getRenewalDate()
    {
        $renewalDate = null;
        $now = new DateTime();
        if ($this->signed_at != null) {
            $year = date('Y');
            $renewalDate = new DateTime($year . $this->signed_at->format('-m-d'));
            if ($now >= $renewalDate) {
                $renewalDate = $renewalDate->modify('+1 year');
            }
        }
        return $renewalDate;
    }

protected function _getPaymentPlanSendingDate()
{
    if ($this->signed_at !== null) {
        $year = date('Y');
        $renewalDate = new DateTime($year . $this->signed_at->format('-m-d'));

        $now = DateTime::now();
        $limit = $now->addDays(61);

        return ($renewalDate >= $now && $renewalDate <= $limit);
    }

    return false;
}

    protected function _getSignedAt($signed_at)
    {
        // if ($this->signature_provider == 'signaturit' and $signed_at != null) {
        //     // create a $dt object with the UTC timezone
        //     $dt = new \DateTime($signed_at->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        //     // change the timezone of the object without changing its time
        //     $dt->setTimezone(new \DateTimeZone('Europe/Paris'));
        //     // $dt = new \DateTime($signed_at->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        //     // $dt->setTimezone(new \DateTimeZone('Europe/Paris'));

        //     return new FrozenTime($dt);
        // }
        return $signed_at;
    }

    protected function _getDecryptedIban()
    {
        $key = Key::loadFromAsciiSafeString(Configure::read('Security.cryptoKey'));
        return $this->encrypted_iban != null ? Crypto::decrypt($this->encrypted_iban, $key) : null;
    }
}
