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

class Update extends Common
{
    public function get(
        Db $db,
        Request $request,
    ) {
        $data = $db->get('psrphp_cms_data', '*', [
            'id' => $request->get('id'),
        ]);
        $form = new Builder('编辑数据');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-8'))->addItem(
                    (new Hidden('id', $data['id'])),
                    (new Input('标题', 'title', $data['title']))->set('help', '例如：'),
                    (new Input('值', 'value', $data['value']))->set('help', '例如：'),
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request,
    ) {
        $data = $db->get('psrphp_cms_data', '*', [
            'id' => $request->post('id'),
        ]);

        $value = $request->post('value');
        if ($db->get('psrphp_cms_data', '*', [
            'dict_id' => $data['dict_id'],
            'value' => $value,
            'id[!]' => $data['id'],
        ])) {
            return Response::error('值不能重复');
        }

        $update = array_intersect_key($request->post(), [
            'title' => '',
            'value' => '',
        ]);

        $db->update('psrphp_cms_data', $update, [
            'id' => $data['id'],
        ]);

        return Response::success('操作成功！');
    }
}
