<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

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
        $field = $db->get('psrphp_cms_field', '*', [
            'id' => $request->get('id'),
        ]);
        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $field['model_id'],
        ]);

        $fields = $db->query('SHOW COLUMNS FROM <psrphp_cms_content_' . $model['name'] . '>')->fetchAll();
        $find = false;
        foreach ($fields as $vo) {
            if ($vo['Field'] == $field['name']) {
                $find = true;
                break;
            }
        }
        if ($find) {
            $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> DROP ' . $field['name']);
        }

        $db->delete('psrphp_cms_field', [
            'id' => $request->get('id'),
        ]);

        return Response::success('操作成功！');
    }
}
