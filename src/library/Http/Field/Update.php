<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Code;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Radio;
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
                    (new Radio('是否允许后台列表显示', 'adminlist', $field['adminlist'], [
                        '0' => '不允许',
                        '1' => '允许',
                    ])),
                    (new Code('后台显示模板', 'adminlisttpl', $field['adminlisttpl']))->set('help', '自定义显示模板，额外变量：$field, $value, $content'),
                    ...($field['type']::getUpdateFieldForm($field) ?: [])
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

        $update = [
            'title' => $request->post('title'),
            'adminlist' => $request->post('adminlist', 0),
            'adminlisttpl' => strlen($request->post('adminlisttpl', '')) ? $request->post('adminlisttpl', '') : null,
            'adminedit' => $request->post('adminedit', 0),
            'adminfilter' => $request->post('adminfilter', 0),
            'adminorder' => $request->post('adminorder', 0),
        ];

        $diff = array_diff_key($request->post(), $update, ['id' => '']);
        $update['extra'] = json_encode(array_merge(json_decode($field['extra'], true), $diff), JSON_UNESCAPED_UNICODE);

        $db->update('psrphp_cms_field', $update, [
            'id' => $field['id'],
        ]);

        return Response::success('操作成功！');
    }
}
