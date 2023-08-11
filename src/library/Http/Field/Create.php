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

class Create extends Common
{
    public function get(
        Db $db,
        Request $request,
    ) {
        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->get('model_id'),
        ]);

        $form = new Builder('添加字段');
        $type = $request->get('type');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-9'))->addItem(
                    (new Hidden('model_id', $model['id'])),
                    (new Hidden('type', $request->get('type'))),
                    (new Input('标题', 'title', '')),
                    (new Input('字段名称', 'name', ''))->set('help', '字段名称只能由字母开头，字母、数字、下划线组成'),
                    (new Input('类型', 'typedisabled', $type::getTitle()))->set('disabled', true),
                    ...($type::onCreateFieldForm() ?: [])
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request
    ) {
        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->post('model_id'),
        ]);

        $name = $request->post('name');
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,78}[A-Za-z0-9]$/', $name)) {
            return Response::error("字段名称只能由字母开头，字母、数字、下划线组成");
        }

        if ($db->get('psrphp_cms_field', '*', [
            'model_id' => $model['id'],
            'name' => $name,
        ])) {
            return Response::error("字段名称不能重复");
        }

        $type = $request->post('type');
        $data = [
            'model_id' => $model['id'],
            'type' => $type,
            'name' => $name,
            'title' => $request->post('title'),
            'adminedit' => $request->post('adminedit', 0),
            'adminlist' => $request->post('adminlist', 0),
            'adminfilter' => $request->post('adminfilter', 0),
            'adminorder' => $request->post('adminorder', 0),
            'adminsearch' => $request->post('adminsearch', 0),
        ];
        $data['extra'] = json_encode(array_diff_key($request->post(), $data), JSON_UNESCAPED_UNICODE);

        $db->insert('psrphp_cms_field', $data);

        $type::onCreateFieldData();
        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }
}
