<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Data;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Col;
use PsrPHP\Form\Row;
use PsrPHP\Form\Input;
use PsrPHP\Request\Request;

class Create extends Common
{
    public function get(
        Request $request,
    ) {
        $form = new Builder('添加数据');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-9'))->addItem(
                    (new Input('dict_id', 'dict_id', $request->get('dict_id')))->setType('hidden'),
                    (new Input('parent', 'parent', $request->get('parent')))->setType('hidden'),
                    (new Input('标题', 'title')),
                    (new Input('别名', 'alias')),
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request
    ) {
        $dict = $db->get('psrphp_cms_dict', '*', [
            'id' => $request->post('dict_id'),
        ]);
        $alias = $request->post('alias');
        if ($db->get('psrphp_cms_data', '*', [
            'dict_id' => $dict['id'],
            'alias' => $alias,
        ])) {
            return Response::error('别名不能重复');
        }
        $db->insert('psrphp_cms_data', [
            'dict_id' => $dict['id'],
            'parent' => $request->post('parent') == '' ? null : $request->post('parent'),
            'title' => $request->post('title'),
            'alias' => $alias,
            'value' => $this->getVals($db->select('psrphp_cms_data', 'value', [
                'dict_id' => $dict['id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]))
        ]);

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }

    private function getVals(array $items): int
    {
        for ($i = 0; $i < count($items) + 10; $i++) {
            if (!in_array($i, $items)) {
                return $i;
            }
        }
    }
}
