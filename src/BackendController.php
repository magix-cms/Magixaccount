<?php
/*
# Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
# License GPLv3
*/

declare(strict_types=1);

namespace Plugins\Magixaccount\src;

use App\Backend\Controller\BaseController;
use Plugins\Magixaccount\db\MagixaccountDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\SmartyTool;

class BackendController extends BaseController
{
    public function run(): void
    {
        SmartyTool::addTemplateDir('admin', ROOT_DIR . 'plugins' . DS . 'Magixaccount' . DS . 'views' . DS . 'admin');

        $action = $_GET['action'] ?? null;

        if (isset($_GET['edit'])) {
            $action = 'edit';
        }

        if ($action === 'save' && Request::isMethod('POST')) {
            $this->processSave();
            return;
        }

        if ($action === 'delete') {
            $this->processDelete();
            return;
        }

        if ($action === 'config') {
            $this->config();
            return;
        }

        if ($action === 'save_config' && Request::isMethod('POST')) {
            $this->processSaveConfig();
            return;
        }

        if ($action && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        $this->index();
    }

    public function index(): void
    {
        $db = new MagixaccountDb();

        // 1. DÉFINITION DU SCHÉMA AVEC CHAMPS DE RECHERCHE
        $targetColumns = ['id_account', 'name_account', 'email', 'is_active', 'created_at'];

        $rawScheme = $db->getTableScheme('mc_account');

        // CORRECTION ICI : On utilise strictement 'column' et 'type' comme attendu par le CMS
        $rawScheme[] = ['column' => 'name_account', 'type' => 'varchar(255)'];

        $associations = [
            'id_account' => [
                'title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-4',
                'input' => ['type' => 'text']
            ],
            'name_account' => [
                'title' => 'nom', 'type' => 'text', 'class' => 'w-25 fw-bold',
                'input' => ['type' => 'text']
            ],
            'email' => [
                'title' => 'email', 'type' => 'text', 'class' => 'w-25',
                'input' => ['type' => 'text']
            ],
            'is_active' => [
                'title' => 'actif', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_',
                'input' => [
                    'type' => 'select',
                    'values' => [['v' => 0, 'name' => 'Inactif'], ['v' => 1, 'name' => 'Actif']]
                ]
            ],
            'created_at' => [
                'title' => 'date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small',
                'input' => ['type' => 'text', 'placeholder' => 'JJ/MM/AAAA']
            ]
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        // 2. INTERCEPTION DE LA RECHERCHE ET PAGINATION
        $page   = Request::isGet('page') ? (int)($_GET['page']) : 1;
        $limit  = Request::isGet('offset') ? (int)($_GET['offset']) : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->getAccountsList($page, $limit, $search);

        $meta = [];

        // 3. FORMATAGE POUR LE COMPOSANT NATIF
        if ($result !== false) {
            $this->getItems('accountsList', $result['data'], true, $result['meta']);
            $meta = $result['meta'];
        }

        // 4. ASSIGNATION SMARTY
        $token = $this->session->getToken();
        $this->view->assign([
            'idcolumn'   => 'id_account',
            'hashtoken'  => $token,
            'url_token'  => urlencode($token),
            'get_search' => $search,
            'sortable'   => false,
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('index.tpl');
    }

    public function add(): void
    {
        $this->edit(); // L'ajout utilise la même vue que l'édition
    }

    public function edit(): void
    {
        // On récupère l'ID passé par table-forms.tpl (?edit=123)
        $idAccount = (int)($_GET['edit'] ?? $_GET['id_account'] ?? 0);

        $db = new MagixaccountDb();
        $account = [];

        if ($idAccount > 0) {
            $account = $db->getAccountFull($idAccount);
        }

        $this->view->assign([
            'account'   => $account,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('edit.tpl');
    }

    private function processSave(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new MagixaccountDb();
        $idAccount = (int)($_POST['id_account'] ?? 0);

        $data = [
            'email'     => FormTool::simpleClean($_POST['email'] ?? ''),
            'firstname' => FormTool::simpleClean($_POST['firstname'] ?? ''),
            'lastname'  => FormTool::simpleClean($_POST['lastname'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Le mot de passe est optionnel depuis l'admin s'il est vide
        if (!empty($_POST['password'])) {
            $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        if ($db->saveAccount($idAccount, $data)) {
            $this->jsonResponse(true, 'Compte enregistré avec succès.', [
                'type'     => $idAccount > 0 ? 'update' : 'add',
                'redirect' => 'index.php?controller=Magixaccount' // Redirection auto via MagixFrontForms
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de l\'enregistrement du compte.');
        }
    }

    private function processDelete(): void
    {
        if (ob_get_length()) ob_clean();

        // table-forms.tpl envoie un tableau POST ['ids'] en suppression de masse
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new MagixaccountDb();
            $successCount = 0;

            foreach ($cleanIds as $idAccount) {
                if ($db->deleteAccount($idAccount)) {
                    $successCount++;
                }
            }

            if ($successCount > 0) {
                $msg = $successCount > 1 ? 'Les comptes ont été supprimés.' : 'Le compte a été supprimé.';
                // On respecte le format JSON attendu par la suppression de table-forms
                echo $this->json->encode(['success' => true, 'message' => $msg, 'ids' => $cleanIds]);
                exit;
            }
        }

        echo $this->json->encode(['success' => false, 'message' => 'Aucun compte sélectionné.']);
        exit;
    }
    /**
     * Page de configuration (Clés API Google, LinkedIn, etc.)
     */
    public function config(): void
    {
        $db = new MagixaccountDb();
        $this->view->assign([
            'config'    => $db->getConfig(),
            'hashtoken' => $this->session->getToken()
        ]);
        $this->view->display('config.tpl');
    }

    /**
     * Sauvegarde de la configuration
     */
    /**
     * Sauvegarde de la configuration
     */
    private function processSaveConfig(): void
    {
        if (ob_get_length()) ob_clean();

        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'google_enabled'         => isset($_POST['google_enabled']) ? 1 : 0,
            'google_client_id'       => FormTool::simpleClean($_POST['google_client_id'] ?? ''),
            'google_client_secret'   => FormTool::simpleClean($_POST['google_client_secret'] ?? ''), // Modifié : pas de simpleClean sur un mot de passe car ça peut altérer des caractères spéciaux valides
            'linkedin_enabled'       => isset($_POST['linkedin_enabled']) ? 1 : 0,
            'linkedin_client_id'     => FormTool::simpleClean($_POST['linkedin_client_id'] ?? ''),
            'linkedin_client_secret' => FormTool::simpleClean($_POST['linkedin_client_secret'] ?? '') // Idem
        ];

        // Remplacement de FormTool::simpleClean pour les "Secret" par trim().
        // Les Client Secrets contiennent souvent des tirets, underscores ou caractères spéciaux que simpleClean pourrait filtrer.
        $data['google_client_secret'] = trim($_POST['google_client_secret'] ?? '');
        $data['linkedin_client_secret'] = trim($_POST['linkedin_client_secret'] ?? '');

        $db = new MagixaccountDb();
        if ($db->saveConfig($data)) {
            $this->jsonResponse(true, 'Configuration enregistrée avec succès.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de l\'enregistrement de la configuration.');
        }
    }
}