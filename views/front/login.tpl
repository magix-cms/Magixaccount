{extends file="layout.tpl"}

{block name='head:title'}Connexion à mon compte{/block}
{block name='head:description'}Connectez-vous à votre espace client pour gérer vos informations.{/block}

{block name="article:content"}
    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => 'Connexion']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="page-header mb-5 mt-3">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">Connexion</h1>
                <p class="lead text-muted">Accédez à votre espace personnel.</p>
            </div>
        </div>
    </header>

    <section class="page-body mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="bg-body p-4 p-md-5 rounded shadow-sm border">
                    <form id="login-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/magixaccount/login">

                        <div class="form-group mb-3">
                            <label for="email" class="form-label fw-bold">Adresse e-mail <span class="text-danger">*</span></label>
                            <input id="email" type="email" name="account[email]" placeholder="votre@email.com" class="form-control" required/>
                        </div>

                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label fw-bold mb-0">Mot de passe <span class="text-danger">*</span></label>
                                <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/forgot" class="small text-decoration-none">Mot de passe oublié ?</a>
                            </div>
                            <input id="password" type="password" name="account[password]" placeholder="Votre mot de passe" class="form-control" required/>
                        </div>

                        <div class="mc-message"></div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
                            </button>
                        </div>

                        {* --- NOUVEAU : BOUTONS SOCIAL LOGIN --- *}
                        {if isset($social_config) && ($social_config.google_enabled == 1 || $social_config.linkedin_enabled == 1)}
                            <div class="position-relative mt-4 mb-4">
                                <hr class="text-muted opacity-25">
                                <span class="position-absolute top-50 start-50 translate-middle bg-body px-2 small text-muted">OU</span>
                            </div>

                            <div class="d-grid gap-2">
                                {if $social_config.google_enabled == 1}
                                    <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/social_login?provider=Google" class="btn btn-outline-dark fw-bold border-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-google text-danger fs-5 me-2"></i> Continuer avec Google
                                    </a>
                                {/if}

                                {if $social_config.linkedin_enabled == 1}
                                    <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/social_login?provider=LinkedIn" class="btn btn-outline-dark fw-bold border-2 d-flex align-items-center justify-content-center" style="color: #0A66C2;">
                                        <i class="bi bi-linkedin fs-5 me-2"></i> Continuer avec LinkedIn
                                    </a>
                                {/if}
                            </div>
                        {/if}
                        {* --- FIN SOCIAL LOGIN --- *}

                        <div class="text-center mt-4">
                            <p class="mb-0 text-muted">Pas encore de compte ? <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/register" class="text-decoration-none fw-bold">S'inscrire</a></p>
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