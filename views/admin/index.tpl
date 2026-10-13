{extends file="layout.tpl"}

{block name='head:title'}MagixAccount - Membres{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-people me-2"></i> Gestion des membres
        </h1>
        {* CORRECTION : Lien classique vers l'action add *}
        <a href="index.php?controller=Magixaccount&action=add" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Ajouter un membre
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 fw-bold text-primary">Liste des comptes clients</h6>
        </div>

        <div class="card-body p-0">
            {include file="components/table-forms.tpl"
            data=$accountsList
            idcolumn='id_account'
            activation=true
            sortable=false
            controller="Magixaccount"
            }
        </div>
    </div>
{/block}