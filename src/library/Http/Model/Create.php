<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Model;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use App\Psrphp\Cms\Model\ModelCreaterProvider;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Select;
use PsrPHP\Request\Request;

class Create extends Common
{
    public function get()
    {
        $form = new Builder('添加模型');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-9'))->addItem(
                    (new Input('标题', 'title')),
                    (new Input('名称', 'name'))->set('help', '名称只能由字母开头，字母、数字、下划线组成，不超过20个字符'),
                    (new Select('类型', 'type', '', (function (): array {
                        $res = [];
                        foreach (ModelCreaterProvider::getInstance()->all() as $vo) {
                            $res[] = [
                                'value' => $vo['type'],
                                'title' => $vo['title'],
                            ];
                        }
                        return $res;
                    })()))
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request
    ) {
        $name = $request->post('name');

        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,18}[A-Za-z0-9]$/', $name)) {
            return Response::error('名称只能由字母开头，字母、数字、下划线组成，不超过20个字符');
        }

        if ($db->get('psrphp_cms_model', '*', [
            'name' => $name,
        ])) {
            return Response::error('模型名称不能重复');
        }

        $type = $request->post('type', '');
        $modelcreater = ModelCreaterProvider::getInstance()->all();
        if (!isset($modelcreater[$type])) {
            return Response::error('类型必选');
        }

        $db->insert('psrphp_cms_model', [
            'title' => $request->post('title'),
            'type' => $type,
            'name' => $name
        ]);
        $model_id = $db->id();

        $db->create('psrphp_cms_content_' . $name, [
            "id" => [
                "INT",
                "NOT NULL",
                "AUTO_INCREMENT"
            ],
            "PRIMARY KEY (<id>)"
        ], [
            "ENGINE" => "MyISAM",
            "AUTO_INCREMENT" => 1
        ]);

        $db->insert('psrphp_cms_field', [
            'model_id' => $model_id,
            'title' => 'ID',
            'name' => 'id',
            'system' => 1,
            'adminedit' => 0,
            'adminlist' => 1,
            'adminfilter' => 0,
            'adminsearch' => 0,
            'adminorder' => 1,
            'extra' => json_encode([]),
        ]);

        $db->insert('psrphp_cms_field', [
            'model_id' => $model_id,
            'title' => '分类名称',
            'name' => 'category_name',
            'system' => 1,
            'adminedit' => 1,
            'adminlist' => 1,
            'adminfilter' => 1,
            'adminsearch' => 0,
            'adminorder' => 0,
            'extra' => json_encode([]),
        ]);
        $db->query('ALTER TABLE <psrphp_cms_content_' . $name . '> ADD category_name varchar(80) NOT NULL DEFAULT \'\' COMMENT \'分类名称\'');

        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ]);
        ModelCreaterProvider::getInstance()->create($type, [
            'model' => $model,
        ]);

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }
}
