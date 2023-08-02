<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Model;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Input;
use PsrPHP\Request\Request;

class Create extends Common
{
    public function get()
    {
        $form = new Builder('添加模型');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-9'))->addItem(
                    (new Input('标题', 'title'))->set('help', '例如：/, /help, /about.html, /page/map.php'),
                    (new Input('名称', 'name'))->set('help', '仅支持英文字母')
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

        $db->insert('psrphp_cms_model', [
            'title' => $request->post('title'),
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
            'is_system' => 1,
            'sortable' => 1,
        ]);

        $db->insert('psrphp_cms_field', [
            'model_id' => $model_id,
            'title' => '模型id',
            'name' => 'model_id',
            'is_system' => 1,
        ]);
        $db->query('ALTER TABLE <psrphp_cms_content_' . $name . '> ADD model_id int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'模型ID\'');

        $db->insert('psrphp_cms_field', [
            'model_id' => $model_id,
            'title' => '分类id',
            'name' => 'category_id',
            'is_system' => 1,
        ]);
        $db->query('ALTER TABLE <psrphp_cms_content_' . $name . '> ADD category_id int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'分类ID\'');

        $db->insert('psrphp_cms_field', [
            'model_id' => $model_id,
            'title' => '创建时间',
            'name' => 'create_time',
            'is_system' => 1,
            'type' => 'datetime-local',
            'filterable' => 1,
            'sortable' => 1,
        ]);
        $db->query('ALTER TABLE <psrphp_cms_content_' . $name . '> ADD create_time int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'创建时间\'');

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }
}
