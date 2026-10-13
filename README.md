# MagixAccount - Espace Membre pour Magix CMS 4

[![Release](https://img.shields.io/github/release/magix-cms/Magixaccount.svg)](https://github.com/magix-cms/Magixaccount/releases/latest)
[![License](https://img.shields.io/github/license/magix-cms/Magixaccount.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-blue.svg)](https://php.net/)
[![Magix CMS](https://img.shields.io/badge/Magix%20CMS-4.x-success.svg)](https://www.magix-cms.com/)

MagixAccount est le plugin officiel de gestion d'utilisateurs pour Magix CMS 4. Il permet à vos visiteurs de s'inscrire, de gérer leur profil, et sert de socle pour sécuriser d'autres plugins (comme les historiques de commandes de Magixcartpay ou les contenus privés).

## 🚀 Fonctionnalités

- **Inscription et Connexion sécurisées** : Gestion des mots de passe hashés et sécurisation native.
- **Gestion de Profil** : Modification des informations personnelles et coordonnées.
- **Historique et Intégrations** : Des Hooks natifs (`accountProfileMenu`, `accountProfileContent`) permettent aux autres plugins d'injecter des données (ex: liste des commandes récentes).
- **Protection de contenu (Digital / Premium)** : Intègre un outil puissant de vérification d'achats via la balise Smarty `{has_purchased}`.

## 📦 Installation

1. Placez le dossier `Magixaccount` dans le répertoire `plugins/` de votre CMS.
2. Rendez-vous dans l'administration de Magix CMS (Gestion des plugins).
3. Cliquez sur **Installer**. Les tables de base de données (ex: `mc_account`) seront générées automatiquement.

## 🎨 Intégration sur le site (Frontend)

Pour permettre à vos utilisateurs d'accéder à leur compte, vous pouvez ajouter des liens dans la navigation de votre thème (généralement dans `skin/votre_theme/layout/header.tpl`).

### 1. Lien vers la connexion / tableau de bord

Le plugin expose ses routes de manière standard. Vous pouvez utiliser ce code Smarty pour afficher dynamiquement le lien de connexion ou le lien vers le profil si l'utilisateur est déjà connecté :

```smarty
{if $session.id_account}
    <a href="{$base_url}{$current_lang.iso_lang|lower}/magixaccount/profile" class="nav-link">
        <i class="bi bi-person-check"></i> Mon Compte
    </a>
{else}
    <a href="{$base_url}{$current_lang.iso_lang|lower}/magixaccount/login" class="nav-link">
        <i class="bi bi-person"></i> Connexion / Inscription
    </a>
{/if}
```

## 🛠️ Utilisation avancée pour les développeurs

### 🔒 Restreindre du contenu lié à un achat (VOD, Fichiers)

Si le plugin `Magixcartpay` est également installé, MagixAccount vous permet de cacher ou d'afficher du contenu conditionnellement si l'utilisateur a payé pour un produit spécifique.

Dans les templates de votre thème (ex: `product.tpl`), utilisez la balise `{has_purchased}` :

```smarty
{* Vérifie si l'utilisateur connecté possède le produit ID 12 avec un statut de commande 'paid' *}
{has_purchased id_product=$product.id assign="user_owns_product"}

{if $user_owns_product}
    <div class="premium-content">
        <h4>Merci pour votre achat !</h4>
        <iframe src="https://www.dailymotion.com/video/privee" allowfullscreen></iframe>
        <a href="{$product.download_link}">Télécharger les sources</a>
    </div>
{else}
    <div class="locked-content">
        <p>Achetez ce produit pour débloquer la vidéo et les fichiers.</p>
        {hook name="displayProductAddToCart" id_product=$product.id}
    </div>
{/if}
```

---

## 📄 Licence

Ce projet est sous licence **GPLv3**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)