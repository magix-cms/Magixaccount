{extends file="layout.tpl"}

{block name='head:title'}Édition du membre{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-person-gear me-2"></i>
            {if isset($account.id_account) && $account.id_account > 0}
                Modifier le membre #{$account.id_account}
            {else}
                Ajouter un membre
            {/if}
        </h1>
        <a href="index.php?controller=Magixaccount" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    {* Ajout de la classe add_form si c'est une création (gère la redirection automatique après succès) *}
    <form action="index.php?controller=Magixaccount&action=save" method="post" class="validate_form {if !isset($account.id_account) || $account.id_account == 0}add_form{/if}">
        <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">
        <input type="hidden" name="id_account" value="{$account.id_account|default:0}">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Informations du compte</h6>
            </div>

            <div class="card-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" value="{$account.firstname|default:''}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nom</label>
                        <input type="text" name="lastname" class="form-control" value="{$account.lastname|default:''}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Adresse E-mail <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{$account.email|default:''}" required autocomplete="off">
                </div>

                <div class="mb-4 bg-light p-3 rounded border">
                    <label class="form-label fw-bold d-flex justify-content-between">
                        <span>Mot de passe</span>
                        <small class="text-muted fw-normal">
                            {if isset($account.id_account) && $account.id_account > 0}
                                Laissez vide pour conserver le mot de passe actuel.
                            {else}
                                <span class="text-danger">Requis pour une création.</span>
                            {/if}
                        </small>
                    </label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" {if !isset($account.id_account) || $account.id_account == 0}required{/if}>
                </div>

                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {if !isset($account.id_account) || $account.is_active == 1}checked{/if}>
                    <label class="form-check-label fw-medium ms-2" for="is_active">Compte actif (autorisé à se connecter)</label>
                </div>
            </div>

            <div class="card-footer bg-light text-end py-3">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i> Enregistrer
                </button>
            </div>
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