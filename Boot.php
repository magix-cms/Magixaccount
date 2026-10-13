<?php
/*
# Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
# License GPLv3
*/

declare(strict_types=1);

namespace Plugins\Magixaccount;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;

class Boot
{
    public function register(): void
    {
        // Accroche pour afficher le widget utilisateur dans le header/navigation
        HookManager::register('displayHeaderUser', 'Magixaccount', [$this, 'hookDisplayHeaderUser']);
    }

    public function hookDisplayHeaderUser(): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins' . DS . 'Magixaccount' . DS . 'views' . DS . 'front' . DS . 'hooks' . DS . 'header_user.tpl');
    }
}