<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Col;
use PsrPHP\Form\Row;
use PsrPHP\Form\Code;
use PsrPHP\Form\Input;
use PsrPHP\Form\Hidden;
use PsrPHP\Form\Radio;
use PsrPHP\Form\Radios;
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
                    (new Input('分组', 'group'))->setRequired(true)->setHelp('例如：基本信息'),
                    (new Input('标题', 'title')),
                    (new Input('字段名称', 'name'))->setHelp('字段名称只能由字母开头，字母、数字、下划线组成'),
                    (new Input('类型', 'type', $type::getTitle()))->setDisabled(true),
                    (new Radios('是否允许后台列表显示'))->addRadio(
                        new Radio('不允许', 'show', 0, true),
                        new Radio('允许', 'show', 1, false),
                    ),
                    (new Code('后台显示模板', 'tpl'))->setHelp('自定义显示模板，额外变量：$field, $value, $content'),
                    ...($type::getCreateFieldForm() ?: [])
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
            'group' => $request->post('group'),
            'title' => $request->post('title'),
            'show' => $request->post('show', 0),
            'tpl' => strlen($request->post('tpl', '')) ? $request->post('tpl', '') : null,
        ];
        $data['extra'] = json_encode(array_diff_key($request->post(), $data), JSON_UNESCAPED_UNICODE);

        $db->insert('psrphp_cms_field', $data);

        $sql = $sql = $type::getCreateFieldSql($model['name'], $name);
        if (strlen($sql)) {
            $db->query($sql);
        }

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }
}
