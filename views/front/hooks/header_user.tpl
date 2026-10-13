{if isset($smarty.session.id_account) && $smarty.session.id_account > 0}
    <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/profile" class="btn btn-outline-primary ms-2" title="Mon Profil">
        <i class="bi bi-person-circle"></i> <span class="d-none d-md-inline ms-1">Mon Espace</span>
    </a>
{else}
    <a href="{$base_url}{$current_lang.iso_lang}/magixaccount/login" class="btn btn-primary ms-2" title="Connexion">
        <i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-md-inline ms-1">Connexion</span>
    </a>
{/if}