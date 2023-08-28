<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Fieldset;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Request\Request;

class Update extends Common
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
        if (!$content = $db->get('psrphp_cms_content_' . $model['name'], '*', [
            'id' => $request->get('id'),
        ])) {
            return Response::error('内容不存在！');
        }

        return (new Builder('编辑内容'))->addItem(
            (new Hidden('model_id', $model['id'])),
            (new Hidden('id', $content['id'])),
            ...(function () use ($db, $model, $content): array {
                $groups = [];
                foreach ($db->select('psrphp_cms_field', '*', [
                    'model_id' => $model['id'],
                    'type[!]' => null,
                ]) as $vo) {
                    $vo['group'] = $vo['group'] ?: '未分组';
                    $groups[$vo['group']][] = $vo;
                }

                $row = new Row;
                foreach ($groups as $group => $fields) {
                    $items = [];
                    foreach ($fields as $field) {
                        $field = array_merge(json_decode($field['extra'], true), $field);
                        if ($tmps = $field['type']::getCreateContentForm($field, $content[$field['name']] ?? null)) {
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
        if (!$content = $db->get('psrphp_cms_content_' . $model['name'], '*', [
            'id' => $request->post('id'),
        ])) {
            return Response::error('内容不存在！');
        }

        $data = [];
        foreach ($db->select('psrphp_cms_field', '*', [
            'model_id' => $model['id'],
        ]) as $field) {
            if ($field['type']) {
                $data[$field['name']] = $field['type']::getUpdateContentData($field, $content[$field['name']]);
            }
        }
        $db->update('psrphp_cms_content_' . $model['name'], $data, [
            'id' => $content['id'],
        ]);
        return Response::success('操作成功！');
    }
}
