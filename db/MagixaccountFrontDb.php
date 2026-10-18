<?php
/*
# Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
# License GPLv3
*/

declare(strict_types=1);

namespace Plugins\Magixaccount\db;

use App\Frontend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class MagixaccountFrontDb extends BaseDb
{
    /**
     * Récupère un compte par son adresse email
     */
    public function getUserByEmail(string $email): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_account')
            ->where('email = :email', ['email' => $email])
            ->limit(1);

        return $this->executeRow($qb) ?: [];
    }

    /**
     * Récupère un compte par son ID, en incluant les données du profil optionnel
     */
    public function getUserById(int $idAccount): array
    {
        $qb = new QueryBuilder();
        // On récupère les infos du compte + celles du profil (company, phone)
        $qb->select('a.id_account, a.email, a.firstname, a.lastname, a.is_active, a.created_at, p.company, p.phone')
            ->from('mc_account AS a')
            // CORRECTION ICI : Séparation en 3 arguments (table, alias, condition)
            ->leftJoin('mc_account_profile', 'p', 'a.id_account = p.id_account')
            ->where('a.id_account = :id', ['id' => $idAccount])
            ->limit(1);

        return $this->executeRow($qb) ?: [];
    }

    /**
     * Vérifie si un e-mail est déjà utilisé par un AUTRE compte
     */
    public function checkEmailExistsForOther(string $email, int $idAccount): bool
    {
        $qb = new QueryBuilder();
        $qb->select('id_account')
            ->from('mc_account')
            ->where('email = :email AND id_account != :id', ['email' => $email, 'id' => $idAccount])
            ->limit(1);

        return (bool)$this->executeRow($qb);
    }

    /**
     * Récupère le hash du mot de passe actuel pour vérification de sécurité
     */
    public function getPasswordHashById(int $idAccount): string
    {
        $qb = new QueryBuilder();
        $qb->select('password_hash')
            ->from('mc_account')
            ->where('id_account = :id', ['id' => $idAccount]);

        $result = $this->executeRow($qb);
        return $result ? (string)$result['password_hash'] : '';
    }

    /**
     * Met à jour le profil du membre, son e-mail, et optionnellement son mot de passe
     */
    public function updateProfile(int $idAccount, array $accountData, array $profileData = [], ?string $newPasswordHash = null): bool
    {
        $updateData = [
            'email'     => $accountData['email'],
            'firstname' => $accountData['firstname'],
            'lastname'  => $accountData['lastname']
        ];

        // On n'ajoute le mot de passe à la requête que s'il a été renseigné et validé
        if ($newPasswordHash !== null) {
            $updateData['password_hash'] = $newPasswordHash;
        }

        // 1. Mise à jour de la table principale (mc_account)
        $qbAccount = new QueryBuilder();
        $qbAccount->update('mc_account', $updateData)->where('id_account = :id', ['id' => $idAccount]);
        $accountUpdated = $this->executeUpdate($qbAccount);

        // 2. Gestion de la table secondaire (mc_account_profile)
        if (!empty($profileData)) {
            $qbCheck = new QueryBuilder();
            $qbCheck->select('id_profile')->from('mc_account_profile')->where('id_account = :id', ['id' => $idAccount]);
            $profileExists = $this->executeRow($qbCheck);

            $qbProfile = new QueryBuilder();
            if ($profileExists) {
                $qbProfile->update('mc_account_profile', [
                    'phone'   => $profileData['phone'] ?? null,
                    'company' => $profileData['company'] ?? null
                ])->where('id_account = :id', ['id' => $idAccount]);
                $this->executeUpdate($qbProfile);
            } else {
                $qbProfile->insert('mc_account_profile', [
                    'id_account' => $idAccount,
                    'phone'      => $profileData['phone'] ?? null,
                    'company'    => $profileData['company'] ?? null
                ]);
                $this->executeInsert($qbProfile);
            }
        }

        return $accountUpdated;
    }

    /**
     * Crée un nouveau compte client
     */
    public function createAccount(array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_account', [
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'firstname'     => $data['firstname'],
            'lastname'      => $data['lastname'],
            'is_active'     => 1
            // CORRECTION: Supprimé car `created_at` est géré nativement par MySQL avec DEFAULT CURRENT_TIMESTAMP
        ]);

        return $this->executeInsert($qb);
    }

    /**
     * EXTENSIBILITÉ : Méthode générique permettant à d'autres plugins
     * (comme un panier, un module de facturation ou de formations) de lier leurs données à l'utilisateur.
     */
    public function getLinkedData(string $tableName, string $foreignKey, int $idAccount): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from($tableName)
            ->where($foreignKey . ' = :id', ['id' => $idAccount])
            ->orderBy('id', 'DESC');

        return $this->executeAll($qb) ?: [];
    }
    /**
     * Sauvegarde un token de réinitialisation de mot de passe
     */
    public function saveResetToken(int $idAccount, string $token, string $expiresAt): bool
    {
        // On nettoie les anciens tokens de cet utilisateur par sécurité
        $qbDelete = new QueryBuilder();
        $qbDelete->delete('mc_account_reset')->where('id_account = :id', ['id' => $idAccount]);
        $this->executeDelete($qbDelete);

        // On insère le nouveau token
        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_account_reset', [
            'id_account' => $idAccount,
            'token'      => $token,
            'expires_at' => $expiresAt
        ]);

        return $this->executeInsert($qbInsert);
    }

    /**
     * Vérifie si un token est valide et retourne l'ID du compte associé
     */
    public function verifyResetToken(string $token): ?int
    {
        $qb = new QueryBuilder();
        $qb->select('id_account')
            ->from('mc_account_reset')
            ->where('token = :token AND expires_at > NOW()', ['token' => $token])
            ->limit(1);

        $result = $this->executeRow($qb);
        return $result ? (int)$result['id_account'] : null;
    }

    /**
     * Met à jour le mot de passe
     */
    public function updatePassword(int $idAccount, string $newPasswordHash): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_account', ['password_hash' => $newPasswordHash])
            ->where('id_account = :id', ['id' => $idAccount]);

        if ($this->executeUpdate($qb)) {
            // Si le mot de passe est mis à jour, on supprime le token utilisé
            $qbDelete = new QueryBuilder();
            $qbDelete->delete('mc_account_reset')->where('id_account = :id', ['id' => $idAccount]);
            $this->executeDelete($qbDelete);
            return true;
        }
        return false;
    }

    /**
     * Récupère la configuration des réseaux sociaux
     */
    public function getSocialConfig(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_magixaccount_config')->limit(1);
        return $this->executeRow($qb) ?: [];
    }

    /**
     * Cherche un utilisateur via son ID réseau social (Google, LinkedIn...)
     */
    public function getUserBySocialProvider(string $provider, string $providerUid): array
    {
        $qb = new QueryBuilder();
        $qb->select('a.*')
            ->from('mc_account', 'a')
            ->join('mc_account_social', 's', 'a.id_account = s.id_account')
            ->where('s.provider = :provider AND s.provider_uid = :uid', [
                'provider' => $provider,
                'uid'      => $providerUid
            ])
            ->limit(1);

        return $this->executeRow($qb) ?: [];
    }

    /**
     * Lie un compte réseau social à un compte Magix existant
     */
    public function linkSocialAccount(int $idAccount, string $provider, string $providerUid): bool
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_account_social', [
            'id_account'   => $idAccount,
            'provider'     => $provider,
            'provider_uid' => $providerUid
        ]);
        return $this->executeInsert($qb);
    }
}