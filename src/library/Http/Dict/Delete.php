<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Dict;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;

class Delete extends Common
{
    public function get(
        Db $db,
        Request $request,
    ) {
        $dict = $db->get('psrphp_cms_dict', '*', [
            'id' => $request->get('id'),
        ]);
        $db->delete('psrphp_cms_dict', [
            'id' => $dict['id']
        ]);
        $db->delete('psrphp_cms_data', [
            'dict_id' => $dict['id']
        ]);
        return Response::success('操作成功！');
    }
}
