<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Data;

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
        $data = $db->get('psrphp_cms_data', '*', [
            'id' => $request->get('id'),
        ]);

        $datas = $db->select('psrphp_cms_data', '*', [
            'dict_id' => $data['dict_id'],
            'parent' => $data['parent'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);

        $count = $db->count('psrphp_cms_data', [
            'dict_id' => $data['dict_id'],
            'parent' => $data['parent'],
            'id[!]' => $data['id'],
            'priority[<=]' => $data['priority'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);
        $change_key = $type == 'up' ? $count + 1 : $count - 1;

        if ($change_key < 0) {
            return Response::error('已经是最有一位了！');
        }
        if ($change_key > count($datas) - 1) {
            return Response::error('已经是第一位了！');
        }
        $datas = array_reverse($datas);
        foreach ($datas as $key => $vo) {
            if ($key == $change_key) {
                $db->update('psrphp_cms_data', [
                    'priority' => $count,
                ], [
                    'id' => $vo['id'],
                ]);
            } elseif ($key == $count) {
                $db->update('psrphp_cms_data', [
                    'priority' => $change_key,
                ], [
                    'id' => $vo['id'],
                ]);
            } else {
                $db->update('psrphp_cms_data', [
                    'priority' => $key,
                ], [
                    'id' => $vo['id'],
                ]);
            }
        }
        return Response::redirect($_SERVER['HTTP_REFERER']);
    }
}
