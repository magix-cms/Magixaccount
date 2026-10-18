{extends file="layout.tpl"}

{block name='head:title'}Configuration MagixAccount{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-gear me-2"></i> Configuration des connexions sociales
        </h1>
        <a href="index.php?controller=Magixaccount" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour aux membres
        </a>
    </div>

    <form action="index.php?controller=Magixaccount&action=save_config&ajax=true" method="post" class="validate_form">
        <input type="hidden" name="hashtoken" value="{$hashtoken}">

        <div class="row g-4">
            {* --- CONFIGURATION GOOGLE --- *}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold" style="color: #DB4437;"><i class="bi bi-google me-2"></i> Connexion avec Google</h6>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="google_enabled" id="google_enabled" value="1" {if isset($config.google_enabled) && $config.google_enabled == 1}checked{/if}>
                            <label class="form-check-label small text-muted" for="google_enabled">Activer</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Client ID</label>
                            <input type="text" name="google_client_id" class="form-control" value="{$config.google_client_id|default:''}">
                        </div>
                        <div>
                            <label class="form-label fw-bold">Client Secret</label>
                            <input type="password" name="google_client_secret" class="form-control" value="{$config.google_client_secret|default:''}">
                        </div>
                        <div class="mt-3 small text-muted">
                            <i class="bi bi-info-circle me-1"></i> <strong>URI de redirection autorisée :</strong><br>
                            <code>{$site_url|trim:'/'}/fr/magixaccount/social_callback?provider=Google</code>
                        </div>
                    </div>
                </div>
            </div>

            {* --- CONFIGURATION LINKEDIN --- *}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold" style="color: #0A66C2;"><i class="bi bi-linkedin me-2"></i> Connexion avec LinkedIn</h6>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="linkedin_enabled" id="linkedin_enabled" value="1" {if isset($config.linkedin_enabled) && $config.linkedin_enabled == 1}checked{/if}>
                            <label class="form-check-label small text-muted" for="linkedin_enabled">Activer</label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Client ID</label>
                            <input type="text" name="linkedin_client_id" class="form-control" value="{$config.linkedin_client_id|default:''}">
                        </div>
                        <div>
                            <label class="form-label fw-bold">Client Secret</label>
                            <input type="password" name="linkedin_client_secret" class="form-control" value="{$config.linkedin_client_secret|default:''}">
                        </div>
                        <div class="mt-3 small text-muted">
                            <i class="bi bi-info-circle me-1"></i> <strong>URI de redirection autorisée :</strong><br>
                            <code>{$site_url|trim:'/'}/fr/magixaccount/social_callback?provider=LinkedIn</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-save me-2"></i> Enregistrer la configuration
            </button>
        </div>
    </form>
{/block}

{block name="javascripts" append}
    <script src="templates/js/MagixFormTools.min.js?v={$smarty.now}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new MagixFormTools();
        });
    </script>
{/block}