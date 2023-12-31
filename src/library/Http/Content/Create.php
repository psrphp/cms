<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Col;
use PsrPHP\Form\Fieldset;
use PsrPHP\Form\Row;
use PsrPHP\Form\Hidden;
use PsrPHP\Request\Request;

class Create extends Common
{
    public function get(
        Db $db,
        Request $request,
    ) {
        if (!$model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->get('model_id'),
        ])) {
            return Response::error('模型不存在！');
        }

        $content = $db->get('psrphp_cms_content_' . $model['name'], '*', [
            'id' => $request->get('copyfrom'),
        ]) ?: [];

        return (new Builder('创建内容'))->addItem(
            (new Hidden('model_id', $model['id'])),
            ...(function () use ($db, $model, $content): array {
                $groups = [];
                foreach ($db->select('psrphp_cms_field', '*', [
                    'model_id' => $model['id'],
                ]) as $vo) {
                    if ($vo['type']) {
                        $groups[$vo['group'] ?: '未分组'][] = $vo;
                    }
                }

                $row = new Row;
                foreach ($groups as $group => $fields) {
                    $items = [];
                    foreach ($fields as $field) {
                        $field = array_merge(json_decode($field['extra'], true), $field);
                        if ($tmps = $field['type']::getCreateContentForm($field, $content)) {
                            array_push($items, ...$tmps);
                        }
                    }
                    if ($items) {
                        $row->addCol(
                            (new Col)->addItem(
                                (new Fieldset((string)$group))->addItem(...$items)
                            )
                        );
                    }
                }
                return [$row];
            })(),
        );
    }

    public function post(
        Db $db,
        Request $request
    ) {
        if (!$model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->post('model_id'),
        ])) {
            return Response::error('模型不存在！');
        }

        $data = [];
        foreach ($db->select('psrphp_cms_field', '*', [
            'model_id' => $model['id'],
        ]) as $field) {
            if ($field['type']) {
                $field = array_merge(json_decode($field['extra'], true), $field);
                $field['type']::getCreateContentData($field, $data);
            }
        }
        $db->insert('psrphp_cms_content_' . $model['name'], $data);
        return Response::success('操作成功！');
    }
}
