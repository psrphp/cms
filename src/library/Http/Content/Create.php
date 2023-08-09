<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use App\Psrphp\Cms\Model\CategoryProvider;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Field\Checkbox;
use PsrPHP\Form\Field\Code;
use PsrPHP\Form\Field\Cover;
use PsrPHP\Form\Field\Files;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Pics;
use PsrPHP\Form\Field\Select;
use PsrPHP\Form\Field\SimpleMDE;
use PsrPHP\Form\Field\Summernote;
use PsrPHP\Form\Field\Textarea;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;

class Create extends Common
{
    public function get(
        Db $db,
        Router $router,
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
            ...(function () use ($db, $model, $content, $router): array {
                $res = [];
                foreach ($db->select('psrphp_cms_field', '*', [
                    'model_id' => $model['id'],
                    'adminedit' => 1,
                ]) as $vo) {
                    $extra = is_null($vo['extra']) ? [] : json_decode($vo['extra'], true);
                    switch ($vo['type']) {
                        case 'select':
                            $res[] = new Select($vo['title'], $vo['name'], $content[$vo['name']] ?? '', (function () use ($db, $vo, $extra): array {
                                return $db->select('psrphp_cms_data', '*', [
                                    'dict_id' => $extra['dict_id'],
                                    'ORDER' => [
                                        'priority' => 'DESC',
                                        'id' => 'ASC',
                                    ],
                                ]);
                            })());
                            break;
                        case 'checkbox':
                            $val = $content[$vo['name']] ?? 0;
                            $vals = [];
                            for ($i = 0; $i < 32; $i++) {
                                $pow = pow(2, $i);
                                if (($val & $pow) == $pow) {
                                    $vals[] = $i;
                                }
                            }
                            $res[] = new Checkbox($vo['title'], $vo['name'], $vals, (function () use ($db, $extra): array {
                                $res = [];
                                foreach ($db->select('psrphp_cms_data', '*', [
                                    'dict_id' => $extra['dict_id'],
                                    'parent' => null,
                                    'ORDER' => [
                                        'priority' => 'DESC',
                                        'id' => 'ASC',
                                    ],
                                ]) as $data) {
                                    $res[$data['value']] = $data['title'];
                                }
                                return $res;
                            })());
                            break;
                        case 'text':
                            $res[] = new Input($vo['title'], $vo['name'], $content[$vo['name']] ?? '');
                            break;
                        case 'textarea':
                            $res[] = new Textarea($vo['title'], $vo['name'], $content[$vo['name']] ?? '');
                            break;
                        case 'code':
                            $res[] = new Code($vo['title'], $vo['name'], $content[$vo['name']] ?? '');
                            break;
                        case 'markdown':
                            $res[] = new SimpleMDE($vo['title'], $vo['name'], $content[$vo['name']] ?? '', $router->build('/psrphp/admin/tool/upload'));
                            break;
                        case 'editor':
                            $res[] = new Summernote($vo['title'], $vo['name'], $content[$vo['name']] ?? '', $router->build('/psrphp/admin/tool/upload'));
                            break;
                        case 'int':
                            $res[] = new Input($vo['title'], $vo['name'], $content[$vo['name']] ?? '', [
                                'type' => 'number',
                                'step' => 1,
                                'min' => $extra['min'] ?? 0,
                                'max' => $extra['max'] ?? 100,
                            ]);
                            break;
                        case 'float':
                            $res[] = new Input($vo['title'], $vo['name'], $content[$vo['name']] ?? '', [
                                'type' => 'number',
                                'min' => $extra['min'] ?? 0,
                                'max' => $extra['max'] ?? 100,
                            ]);
                            break;
                        case 'time':
                            $res[] = new Input($vo['title'], $vo['name'], $content[$vo['name']] ?? '', [
                                'type' => 'time',
                            ]);
                            break;
                        case 'date':
                            $res[] = new Input($vo['title'], $vo['name'], $content[$vo['name']] ?? '', [
                                'type' => 'date',
                            ]);
                            break;
                        case 'datetime':
                            $res[] = new Input($vo['title'], $vo['name'], $content[$vo['name']] ?? '', [
                                'type' => 'datetime-local',
                            ]);
                            break;
                        case 'pic':
                            $res[] = new Cover($vo['title'], $vo['name'], $content[$vo['name']] ?? '', $router->build('/psrphp/admin/tool/upload'));
                            break;
                        case 'pics':
                            if (isset($content[$vo['name']]) && strlen($content[$vo['name']])) {
                                $val = json_decode($content[$vo['name']], true);
                            } else {
                                $val = [];
                            }
                            $res[] = new Pics($vo['title'], $vo['name'], $val, $router->build('/psrphp/admin/tool/upload'));
                            break;
                        case 'files':
                            if (isset($content[$vo['name']]) && strlen($content[$vo['name']])) {
                                $val = json_decode($content[$vo['name']], true);
                            } else {
                                $val = [];
                            }
                            $res[] = new Files($vo['title'], $vo['name'], $val, $router->build('/psrphp/admin/tool/upload'));
                            break;

                        default:
                            break;
                    }
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
            switch ($field['type']) {
                case 'pics':
                case 'files':
                    $data[$field['name']] = json_encode(
                        $request->post($field['name'], []),
                        JSON_UNESCAPED_UNICODE
                    );
                    break;

                case 'checkbox':
                    $data[$field['name']] = 0;
                    foreach ($request->post($field['name'], []) as $v) {
                        $data[$field['name']] += pow(2, $v);
                    }
                    break;

                default:
                    if ($request->has('post.' . $field['name'])) {
                        $data[$field['name']] = $request->post($field['name']);
                    }
                    break;
            }
        }
        $db->insert('psrphp_cms_content_' . $model['name'], $data);
        return Response::success('操作成功！');
    }
}
