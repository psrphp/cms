<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;

class Priority extends Common
{
    public function get(
        Db $db,
        Request $request,
    ) {
        $type = $request->get('type');
        $field = $db->get('psrphp_cms_field', '*', [
            'id' => $request->get('id'),
        ]);

        $fields = $db->select('psrphp_cms_field', '*', [
            'model_id' => $field['model_id'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);

        $count = $db->count('psrphp_cms_field', [
            'model_id' => $field['model_id'],
            'id[!]' => $field['id'],
            'priority[<=]' => $field['priority'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);
        $change_key = $type == 'up' ? $count + 1 : $count - 1;

        if ($change_key < 0) {
            return Response::error('已经是最有一位了！');
        }
        if ($change_key > count($fields) - 1) {
            return Response::error('已经是第一位了！');
        }
        $fields = array_reverse($fields);
        foreach ($fields as $key => $vo) {
            if ($key == $change_key) {
                $db->update('psrphp_cms_field', [
                    'priority' => $count,
                ], [
                    'id' => $vo['id'],
                ]);
            } elseif ($key == $count) {
                $db->update('psrphp_cms_field', [
                    'priority' => $change_key,
                ], [
                    'id' => $vo['id'],
                ]);
            } else {
                $db->update('psrphp_cms_field', [
                    'priority' => $key,
                ], [
                    'id' => $vo['id'],
                ]);
            }
        }
        return Response::redirect($_SERVER['HTTP_REFERER']);
    }
}
