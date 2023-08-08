<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Model;

use App\Psrphp\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Config;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Db $db,
        Config $config,
        Template $template
    ) {
        $this->test($config);
        $models = $db->select('psrphp_cms_model', '*');
        return $template->renderFromFile('model/index@psrphp/cms', [
            'models' => $models,
        ]);
    }

    public function test(
        Config $config
    ) {
        // $config->get('app.install@psrphp/cms')();
    }
}
