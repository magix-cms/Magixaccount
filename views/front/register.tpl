{extends file="layout.tpl"}

{block name='head:title'}Créer un compte{/block}
{block name='head:description'}Inscrivez-vous pour accéder à votre espace client.{/block}

{block name="article:content"}
    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => 'Inscription']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="page-header mb-5 mt-3">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">Créer un compte</h1>
                <p class="lead text-muted">Remplissez le formulaire ci-dessous pour vous inscrire.</p>
            </div>
        </div>
    </header>

    <section class="page-body mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="bg-body p-4 p-md-5 rounded shadow-sm border">
                    <form id="register-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/magixaccount/register">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="firstname" class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                    <input id="firstname" type="text" name="account[firstname]" class="form-control" required/>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="lastname" class="form-label fw-bold">Nom</label>
                                    <input id="lastname" type="text" name="account[lastname]" class="form-control"/>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label fw-bold">Adresse e-mail <span class="text-danger">*</span></label>
                            <input id="email" type="email" name="account[email]" class="form-control" required/>
                        </div>

                        <div class="form-group mb-4">
                            <label for="password" class="form-label fw-bold">Mot de passe <span class="text-danger">*</span></label>
                            <input id="password" type="password" name="account[password]" class="form-control" required minlength="8"/>
                        </div>

                        {* CHAMP HONEYPOT ANTI-SPAM (Caché aux humains via CSS) *}
                        <div style="display:none; visibility:hidden; position:absolute; left:-9999px;">
                            <label for="website_url">Laissez ce champ vide</label>
                            <input type="text" id="website_url" name="website_url" value="" autocomplete="off" tabindex="-1">
                        </div>

                        <div class="mc-message"></div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i> M'inscrire
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <p class="mb-0 text-muted">Déjà un compte ? <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/login" class="text-decoration-none fw-bold">Se connecter</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
{/block}

{block name="javascript_data"}
    {$page_js = [
    'defer' => ['MagixToast','MagixFrontForms']
    ] scope="parent"}
{/block}