<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

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
        $field = $db->get('psrphp_cms_field', '*', [
            'id' => $request->get('id'),
        ]);
        $field = array_merge(json_decode($field['extra'], true), $field);
        $form = new Builder('编辑字段');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-8'))->addItem(
                    (new Hidden('id', $field['id'])),
                    (new Input('标题', 'title', $field['title']))->set('help', '例如：'),
                    (new Input('字段', 'name00', $field['name']))->set('disabled', true),
                    (new Input('类型', 'typedisabled', $field['type']::getTitle()))->set('disabled', true),
                    ...($field['type']::onUpdateFieldForm($field) ?: [])
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request,
    ) {
        $field = $db->get('psrphp_cms_field', '*', [
            'id' => $request->post('id'),
        ]);

        $update = array_intersect_key($request->post(), [
            'title' => '',
            'adminedit' => '',
            'adminlist' => '',
            'adminfilter' => '',
            'adminorder' => '',
            'adminsearch' => '',
        ]);

        $diff = array_diff_key($request->post(), $update, ['id' => '']);
        $update['extra'] = json_encode(array_merge(json_decode($field['extra'], true), $diff), JSON_UNESCAPED_UNICODE);

        $db->update('psrphp_cms_field', $update, [
            'id' => $field['id'],
        ]);

        return Response::success('操作成功！');
    }
}
