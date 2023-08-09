<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Data;

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
        if (!$item = $db->get('psrphp_cms_data', '*', [
            'id' => $request->get('id'),
        ])) {
            return Response::error('数据不存在');
        }
        $this->deleteSub($db, $item);
        return Response::success('操作成功！');
    }

    private function deleteSub(Db $db, array $item)
    {
        foreach ($db->select('psrphp_cms_data', '*', [
            'parent' => $item['value'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]) as $vo) {
            $this->deleteSub($db, $vo);
        }
        $db->delete('psrphp_cms_data', [
            'id' => $item['id'],
        ]);
    }
}
