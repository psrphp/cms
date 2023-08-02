<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Data;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
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
                    (new Hidden('dict_id', $request->get('dict_id', 0))),
                    (new Hidden('pid', $request->get('pid', 0))),
                    (new Input('标题', 'title'))->set('help', '例如：'),
                    (new Input('值', 'value'))->set('help', '例如：'),
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
        $db->insert('psrphp_cms_data', [
            'dict_id' => $dict['id'],
            'pid' => $request->post('pid', 0),
            'title' => $request->post('title'),
            'value' => $request->post('value'),
            'sn' => $this->getSn($db->select('psrphp_cms_data', 'sn', [
                'dict_id' => $dict['id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]))
        ]);

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }

    private function getSn(array $values): int
    {
        for ($i = 0; $i < count($values) + 10; $i++) {
            if (!in_array($i, $values)) {
                return $i;
            }
        }
    }
}
