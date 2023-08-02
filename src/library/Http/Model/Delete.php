<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Model;

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
        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->get('id'),
        ]);
        $db->drop('psrphp_cms_content_' . $model['name']);
        $db->delete('psrphp_cms_model', [
            'id' => $request->get('id'),
        ]);
        $db->delete('psrphp_cms_field', [
            'model_id' => $model['id'],
        ]);
        return Response::success('操作成功！');
    }
}
