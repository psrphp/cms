<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Dict;

use App\Psrphp\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Template\Template;

class Index extends Common
{

    public function get(
        Db $db,
        Template $template
    ) {
        $dicts = $db->select('psrphp_cms_dict', '*');
        return $template->renderFromFile('dict/index@psrphp/cms', [
            'dicts' => $dicts,
        ]);
    }
}
