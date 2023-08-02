<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;

class Move extends Common
{
    public function post(
        Db $db,
        Request $request,
    ) {
        if (!$model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->post('model_id'),
        ])) {
            return Response::error('模型不存在！');
        }
        $db->update('psrphp_cms_content_' . $model['name'], [
            'category_id' => $request->post('category_id'),
        ], [
            'id' => $request->post('ids'),
        ]);
        return Response::success('操作成功！');
    }
}
