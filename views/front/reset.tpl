{extends file="layout.tpl"}

{block name='head:title'}Nouveau mot de passe{/block}
{block name='head:description'}Définissez un nouveau mot de passe pour votre compte.{/block}

{block name="article:content"}
    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => 'Réinitialisation du mot de passe']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="page-header mb-5 mt-3">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">Nouveau mot de passe</h1>
                <p class="lead text-muted">Veuillez saisir votre nouveau mot de passe sécurisé.</p>
            </div>
        </div>
    </header>

    <section class="page-body mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="bg-body p-4 p-md-5 rounded shadow-sm border">
                    <form id="reset-form" class="validate_form" method="post" action="{$base_url}{$current_lang.iso_lang}/magixaccount/reset">

                        {* Token caché généré par le contrôleur *}
                        <input type="hidden" name="token" value="{$reset_token|default:''}">

                        <div class="form-group mb-3">
                            <label for="password" class="form-label fw-bold">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <input id="password" type="password" name="account[password]" class="form-control" required minlength="8"/>
                        </div>

                        <div class="form-group mb-4">
                            <label for="password_confirm" class="form-label fw-bold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input id="password_confirm" type="password" name="account[password_confirm]" class="form-control" required minlength="8"/>
                        </div>

                        <div class="mc-message"></div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i> Mettre à jour mon mot de passe
                            </button>
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