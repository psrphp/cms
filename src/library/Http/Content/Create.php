<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use App\Psrphp\Cms\Model\CategoryProvider;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Select;
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
            (new Select('栏目', 'category_name', $content['category_name'] ?? '', (function () use ($model): array {
                $res = [];
                foreach (CategoryProvider::getInstance($model['id'])->all() as $vo) {
                    $res[] = [
                        'value' => $vo['name'],
                        'title' => $vo['title'],
                        'parent' => $vo['parent'],
                        'group' => $vo['group'],
                    ];
                }
                return $res;
            })())),
            ...(function () use ($db, $model, $content): array {
                $res = [];
                foreach ($db->select('psrphp_cms_field', '*', [
                    'model_id' => $model['id'],
                    'adminedit' => 1,
                    'type[!]' => null,
                ]) as $field) {
                    array_push($res, ...$field['type']::onCreateContentForm($field, $content[$field['name']]));
                }
                return $res;
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
            // todo..
            if (!$request->has('post.' . $field['name'])) {
                continue;
            }
            if ($field['type']) {
                $data[$field['name']] = $field['type']::onCreateContentData($field);
            }
        }
        $data['category_name'] = $request->post('category_name');
        $db->insert('psrphp_cms_content_' . $model['name'], $data);
        return Response::success('操作成功！');
    }
}
