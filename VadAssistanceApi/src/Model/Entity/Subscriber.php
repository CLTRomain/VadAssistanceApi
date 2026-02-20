<?php
declare(strict_types=1);

namespace App\Model\Entity;

// AJOUTS NÉCESSAIRES
use Authentication\IdentityInterface;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;


/**
 * Subscriber Entity
 *
 * @property int $id
 * @property string|null $uuid
 * @property string|null $customer_number
 * @property int $user_id
 * @property int|null $admin_id
 * @property string|null $email
 * @property bool $email_validated
 * @property string $hash_mail
 * @property string|null $password
 * @property string|null $lost_password
 * @property \Cake\I18n\DateTime|null $lost_password_validity
 * @property string|null $phone
 * @property string|null $civility
 * @property string|null $first_name
 * @property string|null $last_name
 * @property \Cake\I18n\Date|null $birth_date
 * @property string|null $birth_place
 * @property string|null $comment
 * @property string|null $company
 * @property string|null $address
 * @property string|null $address_rest
 * @property string|null $postal_code
 * @property string|null $city
 * @property string $country
 * @property \Cake\I18n\DateTime|null $validated_account
 * @property \Cake\I18n\DateTime|null $activated_account
 * @property \Cake\I18n\DateTime|null $privacy_policy_acceptance
 * @property \Cake\I18n\DateTime|null $partner_offers_acceptance
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Contract[] $contracts
 */
class Subscriber extends Entity implements IdentityInterface // <--- AJOUTEZ L'INTERFACE ICI
{
    /**
     * Méthodes obligatoires de l'IdentityInterface
     * Permet au plugin de récupérer l'ID et les données d'origine
     */
        public function getIdentifier(): array|string|int|null
        {
            return $this->id;
        }

        public function getOriginalData(): \Authentication\IdentityInterface
        {
            return $this;
        }

    protected array $_accessible = [
        'uuid' => true,
        'customer_number' => true,
        'user_id' => true,
        'admin_id' => true,
        'email' => true,
        'email_validated' => true,
        'hash_mail' => true,
        'password' => true,
        'lost_password' => true,
        'lost_password_validity' => true,
        'phone' => true,
        'civility' => true,
        'first_name' => true,
        'last_name' => true,
        'birth_date' => true,
        'birth_place' => true,
        'comment' => true,
        'company' => true,
        'address' => true,
        'address_rest' => true,
        'postal_code' => true,
        'city' => true,
        'country' => true,
        'validated_account' => true,
        'activated_account' => true,
        'privacy_policy_acceptance' => true,
        'partner_offers_acceptance' => true,
        'created' => true,
        'modified' => true,
        'contracts' => true,
    ];

    protected array $_hidden = [
        'password',
    ];

    /**
     * Hashage du mot de passe
     * Utilise le Hasher par défaut de CakePHP (plus sûr pour le plugin)
     */
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return $password;
    }
}