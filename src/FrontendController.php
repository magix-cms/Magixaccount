<?php
/*
# Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
# License GPLv3
*/

declare(strict_types=1);

namespace Plugins\Magixaccount\src;

use App\Frontend\Controller\BaseController;
use Plugins\Magixaccount\db\MagixaccountFrontDb;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\Tool\MailTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\HTTP\Session;

class FrontendController extends BaseController
{
    private Session $sessionManager;
    private MagixaccountFrontDb $db;

    public function __construct()
    {
        parent::__construct();
        // Utilisation de la librairie Session de Magepattern[cite: 21]
        $this->sessionManager = new Session(true, 'magix_sess_id');
        $this->sessionManager->start();
        $this->db = new MagixaccountFrontDb();
    }

    public function run(): void
    {
        SmartyTool::addTemplateDir('front', ROOT_DIR . 'plugins' . DS . 'Magixaccount' . DS . 'views' . DS . 'front');

        $action = $_GET['action'] ?? 'login';

        // --- TRAITEMENT DES FORMULAIRES (POST) ---
        if ($action === 'login' && Request::isMethod('POST')) {
            $this->processLogin();
            return;
        }
        if ($action === 'register' && Request::isMethod('POST')) {
            $this->processRegister();
            return;
        }
        if ($action === 'update' && Request::isMethod('POST')) {
            $this->processUpdateProfile();
            return;
        }
        // NOUVEAU : Traitement de l'oubli de mot de passe
        if ($action === 'forgot' && Request::isMethod('POST')) {
            $this->processForgot();
            return;
        }
        // NOUVEAU : Traitement du nouveau mot de passe
        if ($action === 'reset' && Request::isMethod('POST')) {
            $this->processReset();
            return;
        }

        // --- AFFICHAGE DES VUES (GET) ---
        // NOUVEAU : Ajout de 'forgot' et 'reset' à la liste blanche
        $allowedActions = ['login', 'register', 'profile', 'logout', 'forgot', 'reset'];

        if (in_array($action, $allowedActions) && method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    protected function redirect(string $url): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }

        echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '" /></noscript>';
        exit;
    }

    private function index(): void
    {
        $this->login(); // Mise à jour de l'appel
    }


    private function login(): void
    {
        if ($this->sessionManager->get('id_account')) {
            $this->redirect('/'.$this->currentLang['iso_lang'].'/magixaccount/profile');
        }
        $this->view->display('login.tpl');
    }


    private function register(): void
    {
        $this->view->display('register.tpl');
    }

    private function profile(): void
    {
        $idAccount = (int)$this->sessionManager->get('id_account');
        if (!$idAccount) {
            // CORRECTION : Ajout de la langue dans l'URL de redirection
            $this->redirect('/' . $this->currentLang['iso_lang'] . '/magixaccount/login');
        }

        $userData = $this->db->getUserById($idAccount);
        $myOrders = $this->db->getLinkedData('mc_shop_order', 'id_account', $idAccount);

        $this->view->assign([
            'user'   => $userData,
            'orders' => $myOrders
        ]);

        $this->view->display('profile.tpl');
    }

    private function processLogin(): void
    {
        if (ob_get_length()) ob_clean();

        $form = FormTool::arrayClean($_POST['account'] ?? [], 'content');

        if (empty($form['email']) || empty($form['password'])) {
            $this->jsonResponse(false, 'Veuillez remplir tous les champs.');
        }

        $user = $this->db->getUserByEmail((string)$form['email']);

        if ($user && password_verify((string)$form['password'], $user['password_hash'])) {
            if ((int)$user['is_active'] !== 1) {
                $this->jsonResponse(false, 'Ce compte a été désactivé.');
            }

            $this->sessionManager->regenerate();
            $this->sessionManager->set('id_account', (int)$user['id_account']);
            $this->sessionManager->set('email', $user['email']);

            // Remplace ceci :
            $redirectUrl = '/' . $this->currentLang['iso_lang'] . '/magixaccount/profile';
            $this->jsonResponse(true, 'Connexion réussie', [
                'redirect' => $redirectUrl,
                'url'      => $redirectUrl // MagixFrontForms détectera cette clé pour rediriger
            ]);
        } else {
            $this->jsonResponse(false, 'Identifiants incorrects.');
        }
    }

    private function processRegister(): void
    {
        if (ob_get_length()) ob_clean();

        // 1. LE BOUCLIER HONEYPOT (Anti-bot basique)
        // Si le champ invisible est rempli, c'est un bot ! On simule un succès pour le tromper.
        if (!empty($_POST['website_url'])) {
            $this->jsonResponse(true, 'Inscription réussie. Vous pouvez vous connecter.', ['type' => 'success']);
        }

        $form = FormTool::arrayClean($_POST['account'] ?? [], 'content');

        if (empty($form['email']) || empty($form['password']) || empty($form['firstname'])) {
            $this->jsonResponse(false, 'Veuillez remplir les champs obligatoires.');
        }

        if (!StringTool::isMail((string)$form['email'])) {
            $this->jsonResponse(false, 'Adresse e-mail invalide.');
        }

        // 2. LE BOUCLIER GOOGLE RECAPTCHA v3
        $isHuman = true;
        if (class_exists('\Plugins\GoogleRecaptcha\src\FrontendController')) {
            $recaptcha = new \Plugins\GoogleRecaptcha\src\FrontendController();
            // On spécifie l'action 'register' pour les statistiques Google
            $isHuman = $recaptcha->verify('register');
        }

        if (!$isHuman) {
            $this->jsonResponse(false, 'La vérification de sécurité a échoué. Veuillez réessayer.');
        }

        // 3. VÉRIFICATION DE L'EXISTENCE DU COMPTE
        if (!empty($this->db->getUserByEmail((string)$form['email']))) {
            $this->jsonResponse(false, 'Cet e-mail est déjà utilisé.');
        }

        $data = [
            'email'         => $form['email'],
            'password_hash' => password_hash($form['password'], PASSWORD_DEFAULT),
            'firstname'     => $form['firstname'],
            'lastname'      => $form['lastname'] ?? ''
        ];

        if ($this->db->createAccount($data)) {
            $this->jsonResponse(true, 'Inscription réussie. Vous pouvez vous connecter.', ['type' => 'success']);
        } else {
            $this->jsonResponse(false, 'Erreur technique lors de l\'inscription.');
        }
    }

    private function processUpdateProfile(): void
    {
        if (ob_get_length()) ob_clean();

        $idAccount = (int)$this->sessionManager->get('id_account');
        if (!$idAccount) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        // 1. Récupération et nettoyage
        $accountForm = FormTool::arrayClean($_POST['account'] ?? [], 'content');
        $profileForm = FormTool::arrayClean($_POST['profile'] ?? [], 'content');

        // 2. Validation du nouvel e-mail
        $newEmail = (string)($accountForm['email'] ?? '');
        if (empty($newEmail) || !StringTool::isMail($newEmail)) {
            $this->jsonResponse(false, 'Adresse e-mail invalide.');
        }

        if ($this->db->checkEmailExistsForOther($newEmail, $idAccount)) {
            $this->jsonResponse(false, 'Cette adresse e-mail est déjà utilisée par un autre compte.');
        }

        // 3. Validation et traitement du mot de passe (si l'utilisateur veut le changer)
        $newPasswordHash = null;
        if (!empty($accountForm['new_password'])) {
            if (empty($accountForm['old_password'])) {
                $this->jsonResponse(false, 'Veuillez saisir votre mot de passe actuel pour en définir un nouveau.');
            }
            if ($accountForm['new_password'] !== ($accountForm['confirm_password'] ?? '')) {
                $this->jsonResponse(false, 'Les nouveaux mots de passe ne correspondent pas.');
            }

            // Vérification de sécurité avec l'ancien mot de passe
            $currentHash = $this->db->getPasswordHashById($idAccount);
            if (!password_verify((string)$accountForm['old_password'], $currentHash)) {
                $this->jsonResponse(false, 'Votre mot de passe actuel est incorrect.');
            }

            $newPasswordHash = password_hash($accountForm['new_password'], PASSWORD_DEFAULT);
        }

        // 4. Préparation des données
        $accountData = [
            'email'     => $newEmail,
            'firstname' => $accountForm['firstname'] ?? '',
            'lastname'  => $accountForm['lastname'] ?? ''
        ];

        $profileData = [
            'company' => $profileForm['company'] ?? null,
            'phone'   => $profileForm['phone'] ?? null
        ];

        // 5. Mise à jour de la base de données
        if ($this->db->updateProfile($idAccount, $accountData, $profileData, $newPasswordHash)) {

            // Synchronisation de la session
            $this->sessionManager->set('email', $newEmail);

            if ($newPasswordHash !== null) {
                // Régénération de l'ID de session pour prévenir le vol de session après un changement critique
                $this->sessionManager->regenerate();
            }

            // L'instruction reload=true demande au Javascript de recharger la page proprement
            $this->jsonResponse(true, 'Profil mis à jour avec succès.', ['type' => 'update', 'reload' => true]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    private function logout(): void
    {
        $this->sessionManager->destroy();
        // CORRECTION : Ajout de la langue dans l'URL de redirection
        $this->redirect('/' . $this->currentLang['iso_lang'] . '/magixaccount/login');
    }

    /**
     * Affiche le formulaire de demande de réinitialisation
     */
    private function forgot(): void
    {
        $this->view->display('forgot.tpl');
    }

    /**
     * Traite la demande de réinitialisation et envoie l'e-mail
     */
    private function processForgot(): void
    {
        if (ob_get_length()) ob_clean();

        $form = FormTool::arrayClean($_POST['account'] ?? [], 'content');

        if (empty($form['email']) || !StringTool::isMail((string)$form['email'])) {
            $this->jsonResponse(false, 'Adresse e-mail invalide.');
        }

        $user = $this->db->getUserByEmail((string)$form['email']);

        if (!empty($user)) {
            // 1. Génération d'un token sécurisé
            $token = bin2hex(random_bytes(32)); // Jeton de 64 caractères
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Valide 1 heure

            // 2. Enregistrement en base de données
            $this->db->saveResetToken((int)$user['id_account'], $token, $expiresAt);

            // 3. Préparation et envoi de l'e-mail
            $resetLink = $this->view->getTemplateVars('base_url') . $this->currentLang['iso_lang'] . '/magixaccount/reset?token=' . $token;

            $isSmtp = isset($this->siteSettings['smtp_enabled']['value']) && $this->siteSettings['smtp_enabled']['value'] == '1';
            $mailer = new MailTool($isSmtp ? 'smtp' : 'mail', [
                'setHost'       => $this->siteSettings['set_host']['value'] ?? '',
                'setPort'       => (int)($this->siteSettings['set_port']['value'] ?? 25),
                'setEncryption' => $this->siteSettings['set_encryption']['value'] ?? '',
                'setUsername'   => $this->siteSettings['set_username']['value'] ?? '',
                'setPassword'   => $this->siteSettings['set_password']['value'] ?? '',
            ]);

            $senderEmail = $this->siteSettings['mail_sender']['value'] ?? 'noreply@votresite.com';

            try {
                $mailer->sendTemplate(
                    'front',
                    'emails/reset_password.tpl',
                    ['user' => $user, 'reset_link' => $resetLink],
                    'Réinitialisation de votre mot de passe',
                    'Service Web <' . $senderEmail . '>',
                    [$user['email'] => $user['firstname']],
                    [],
                    $senderEmail
                );
            } catch (\Throwable $e) {
                $this->logger->log("Erreur SMTP Oubli MDP : " . $e->getMessage(), "error");
            }
        }

        // IMPORTANT : On affiche un succès même si l'email n'existe pas (Sécurité anti-énumération)
        $this->jsonResponse(true, 'Si votre adresse e-mail existe, un lien de réinitialisation vous a été envoyé.', ['type' => 'success']);
    }

    /**
     * Affiche le formulaire pour saisir un nouveau mot de passe
     */
    private function reset(): void
    {
        $token = $_GET['token'] ?? '';
        $idAccount = $this->db->verifyResetToken($token);

        if (!$idAccount) {
            // Token invalide ou expiré
            $this->redirect('/' . $this->currentLang['iso_lang'] . '/magixaccount/login?error=token_invalid');
        }

        $this->view->assign('reset_token', $token);
        $this->view->display('reset.tpl');
    }

    /**
     * Traite la sauvegarde du nouveau mot de passe
     */
    private function processReset(): void
    {
        if (ob_get_length()) ob_clean();

        $token = $_POST['token'] ?? '';
        $form = FormTool::arrayClean($_POST['account'] ?? [], 'content');

        if (empty($form['password']) || empty($form['password_confirm'])) {
            $this->jsonResponse(false, 'Veuillez remplir tous les champs.');
        }

        if ($form['password'] !== $form['password_confirm']) {
            $this->jsonResponse(false, 'Les mots de passe ne correspondent pas.');
        }

        $idAccount = $this->db->verifyResetToken($token);

        if (!$idAccount) {
            $this->jsonResponse(false, 'Le lien de réinitialisation est invalide ou a expiré.');
        }

        $newHash = password_hash($form['password'], PASSWORD_DEFAULT);

        if ($this->db->updatePassword($idAccount, $newHash)) {
            $redirectUrl = '/' . $this->currentLang['iso_lang'] . '/magixaccount/login';
            $this->jsonResponse(true, 'Votre mot de passe a été modifié avec succès. Vous pouvez vous connecter.', [
                'redirect' => $redirectUrl,
                'url'      => $redirectUrl
            ]);
        } else {
            $this->jsonResponse(false, 'Une erreur technique est survenue.');
        }
    }
}