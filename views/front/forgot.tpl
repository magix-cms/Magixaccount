{extends file="layout.tpl"}

{block name='head:title'}Mot de passe oublié{/block}
{block name='head:description'}Réinitialisez l'accès à votre espace membre.{/block}

{block name="article:content"}
    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => 'Connexion', 'url' => "`$base_url``$current_lang.iso_lang`/magixaccount/login"],
    ['label' => 'Mot de passe oublié']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="page-header mb-5 mt-3">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">Mot de passe oublié ?</h1>
                <p class="lead text-muted">Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation.</p>
            </div>
        </div>
    </header>

    <section class="page-body mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="bg-body p-4 p-md-5 rounded shadow-sm border">
                    <form id="forgot-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/magixaccount/forgot">

                        <div class="form-group mb-4">
                            <label for="email" class="form-label fw-bold">Adresse e-mail <span class="text-danger">*</span></label>
                            <input id="email" type="email" name="account[email]" placeholder="votre@email.com" class="form-control" required/>
                        </div>

                        <div class="mc-message"></div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-envelope me-2"></i> Envoyer le lien
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/login" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Retour à la connexion</a>
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