<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Data;

use App\Psrphp\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Db $db,
        Request $request,
        Template $template
    ) {
        $dict = $db->get('psrphp_cms_dict', '*', [
            'id' => $request->get('dict_id'),
        ]);
        $datas = $db->select('psrphp_cms_data', '*', [
            'dict_id' => $dict['id'],
            'pid' => $request->get('pid', 0),
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);
        return $template->renderFromFile('data/index@psrphp/cms', [
            'dict' => $dict,
            'datas' => $datas,
        ]);
    }
}
