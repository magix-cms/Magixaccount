<?php
/*
# Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
# License GPLv3
*/

declare(strict_types=1);

namespace Plugins\Magixaccount\db;

use App\Backend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class MagixaccountDb extends BaseDb
{
    /**
     * Récupère la liste de tous les comptes enregistrés pour le tableau de bord
     */
    public function getAccountsList(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();

        // 1. LE CŒUR DE LA REQUÊTE
        $qb->select([
            'id_account',
            "CONCAT_WS(' ', firstname, lastname) AS name_account",
            'email',
            'is_active',
            'created_at'
        ])
            ->from('mc_account');

        // 2. GESTION DE LA RECHERCHE
        if (!empty($search)) {
            $qb->orderBy('id_account', 'DESC');
            $nbc = 1;

            foreach ($search as $key => $q) {
                if ($q !== '') {
                    $paramName = 'p' . $nbc;
                    $binds = [];

                    switch ($key) {
                        case 'id_account':
                            $binds[$paramName] = $q;
                            $qb->where("{$key} = :{$paramName}", $binds);
                            break;
                        case 'is_active':
                            $binds[$paramName] = $q;
                            $qb->where("{$key} = :{$paramName}", $binds);
                            break;
                        case 'email':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("{$key} LIKE :{$paramName}", $binds);
                            break;
                        case 'name_account':
                            $binds[$paramName] = '%' . $q . '%';
                            // Recherche sur la concaténation réelle
                            $qb->where("CONCAT_WS(' ', firstname, lastname) LIKE :{$paramName}", $binds);
                            break;
                        case 'created_at':
                            $formattedDate = DateTool::toSql((string)$q);
                            $binds[$paramName] = '%' . $formattedDate . '%';
                            $qb->where("{$key} LIKE :{$paramName}", $binds);
                            break;
                    }
                    $nbc++;
                }
            }
        } else {
            // 3. COMPORTEMENT PAR DÉFAUT
            $qb->orderBy('id_account', 'DESC');
        }

        // On utilise le paginateur natif
        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un compte complet pour l'édition admin
     */
    public function getAccountFull(int $idAccount): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_account')
            ->where('id_account = :id', ['id' => $idAccount]);

        return $this->executeRow($qb) ?: [];
    }

    /**
     * Sauvegarde ou met à jour un compte depuis l'admin
     */
    public function saveAccount(int $idAccount, array $data): bool
    {
        $qb = new QueryBuilder();
        if ($idAccount > 0) {
            $qb->update('mc_account', $data)->where('id_account = :id', ['id' => $idAccount]);
            return $this->executeUpdate($qb);
        } else {
            $qb->insert('mc_account', $data);
            return $this->executeInsert($qb);
        }
    }

    /**
     * Supprime un compte utilisateur
     */
    public function deleteAccount(int $idAccount): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_account')->where('id_account = :id', ['id' => $idAccount]);
        return $this->executeDelete($qb);
    }

    /**
     * Récupère la configuration du plugin
     */
    public function getConfig(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_magixaccount_config')->limit(1);
        $res = $this->executeRow($qb);

        return $res ?: [];
    }

    /**
     * Sauvegarde la configuration
     */
    public function saveConfig(array $data): bool
    {
        $config = $this->getConfig();
        $qb = new QueryBuilder();

        if (isset($config['id_config'])) {
            $qb->update('mc_magixaccount_config', $data);
            return $this->executeUpdate($qb) !== false;
        } else {
            $qb->insert('mc_magixaccount_config', $data);
            return $this->executeInsert($qb);
        }
    }
}