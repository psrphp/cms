<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Model;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Cms\Psrphp\Script;
use PsrPHP\Database\Db;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Db $db,
        Template $template
    ) {
        // Script::onInstall();
        $models = $db->select('psrphp_cms_model', '*');
        return $template->renderFromFile('model/index@psrphp/cms', [
            'models' => $models,
        ]);
    }
}
