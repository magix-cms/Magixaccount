{extends file="layout.tpl"}

{block name='head:title'}Mon Profil{/block}
{block name='head:description'}Gérez vos informations personnelles et retrouvez votre historique.{/block}

{block name="article:content"}
    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => 'Mon espace', 'url' => "`$base_url``$current_lang.iso_lang`/magixaccount/profile"],
    ['label' => 'Profil']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="page-header mb-4 mt-3">
        <div class="row">
            <div class="col-12">
                <h1 class="display-5 fw-bold text-primary">Bonjour, {$user.firstname|default:'Membre'}</h1>
                <p class="lead text-muted">Bienvenue dans votre espace personnel.</p>
            </div>
        </div>
    </header>

    <section class="page-body mb-5">
        <div class="row g-4">

            {* --- COLONNE GAUCHE : NAVIGATION --- *}
            <aside class="col-12 col-lg-3">
                <div class="list-group shadow-sm border-0">
                    <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/profile" class="list-group-item list-group-item-action active border-0 py-3">
                        <i class="bi bi-person me-2"></i> Mon Profil
                    </a>

                    {* --- HOOK : MENU DES PLUGINS EXTERNES (ex: Panier d'achat) --- *}
                    {event name="displayAccountProfileMenu" id_account=$user.id_account|default:0}

                    <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/logout" class="list-group-item list-group-item-action text-danger border-0 py-3 mt-2">
                        <i class="bi bi-box-arrow-left me-2"></i> Se déconnecter
                    </a>
                </div>
            </aside>

            {* --- COLONNE DROITE : CONTENU DU PROFIL --- *}
            <div class="col-12 col-lg-9">
                <div class="bg-body p-4 p-md-5 rounded shadow-sm border mb-4">
                    <h2 class="h4 mb-4 fw-bold border-bottom pb-2">Mes informations personnelles</h2>

                    <form id="profile-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/magixaccount/update">

                        {* --- DONNÉES DU COMPTE --- *}
                        <h3 class="h6 text-muted mb-3">Identification</h3>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="firstname" class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                    <input id="firstname" type="text" name="account[firstname]" class="form-control" value="{$user.firstname|default:''}" required/>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="lastname" class="form-label fw-bold">Nom</label>
                                    <input id="lastname" type="text" name="account[lastname]" class="form-control" value="{$user.lastname|default:''}"/>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="email" class="form-label fw-bold">Adresse e-mail <span class="text-danger">*</span></label>
                            <input id="email" type="email" name="account[email]" class="form-control" value="{$user.email|default:''}" required/>
                            <div class="form-text">Cette adresse est utilisée pour votre connexion et nos communications.</div>
                        </div>

                        {* --- DONNÉES DE SÉCURITÉ --- *}
                        <h3 class="h6 text-muted mt-4 mb-3">Sécurité <span class="small fw-normal">(Laisser vide si inchangé)</span></h3>
                        <div class="bg-light p-3 rounded border mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group mb-2">
                                        <label for="old_password" class="form-label fw-bold">Mot de passe actuel</label>
                                        <input id="old_password" type="password" name="account[old_password]" class="form-control" placeholder="Requis uniquement pour changer de mot de passe"/>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="new_password" class="form-label fw-bold">Nouveau mot de passe</label>
                                        <input id="new_password" type="password" name="account[new_password]" class="form-control" minlength="8"/>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="confirm_password" class="form-label fw-bold">Confirmer nouveau mot de passe</label>
                                        <input id="confirm_password" type="password" name="account[confirm_password]" class="form-control" minlength="8"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {* --- DONNÉES DU PROFIL --- *}
                        <h3 class="h6 text-muted mt-4 mb-3">Coordonnées</h3>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="company" class="form-label fw-bold">Société</label>
                                    <input id="company" type="text" name="profile[company]" class="form-control" value="{$user.company|default:''}"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label fw-bold">Téléphone</label>
                                    <input id="phone" type="text" name="profile[phone]" class="form-control" value="{$user.phone|default:''}"/>
                                </div>
                            </div>
                        </div>

                        <div class="mc-message"></div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Mettre à jour mon profil
                            </button>
                        </div>
                    </form>
                </div>

                {* --- HOOK : CONTENU DES PLUGINS EXTERNES (ex: Historique de commandes) --- *}
                {event name="displayAccountProfileContent" id_account=$user.id_account|default:0}

            </div>
        </div>
    </section>
{/block}

{block name="javascript_data"}
    {$page_js = [
    'defer' => ['MagixToast','MagixFrontForms']
    ] scope="parent"}
{/block}