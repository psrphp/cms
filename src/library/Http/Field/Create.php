<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Component\SwitchItem;
use PsrPHP\Form\Component\Switchs;
use PsrPHP\Form\Field\Code;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Form\Field\Select;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;

class Create extends Common
{
    public function get(
        Db $db,
        Router $router,
        Request $request,
    ) {
        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->get('model_id'),
        ]);

        $field = [];
        $extra = [];
        $form = new Builder('添加字段');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-9'))->addItem(
                    (new Hidden('model_id', $model['id'])),
                    (new Hidden('type', $request->get('type'))),
                    (new Input('标题', 'title', $field['title'] ?? '')),
                    (new Input('字段名称', 'name', $field['name'] ?? ''))->set('help', '字段名称只能由字母开头，字母、数字、下划线组成'),
                    (new Input('类型', 'typedisabled', [
                        'select' => '单选',
                        'checkbox' => '多选',
                        'text' => '单行文本',
                        'textarea' => '多行文本',
                        'code' => '代码编辑器',
                        'markdown' => 'markdown编辑器',
                        'editor' => '富文本编辑器',
                        'int' => '整数',
                        'float' => '浮点数',
                        'time' => '时间',
                        'date' => '日期',
                        'datetime' => '日期时间',
                        'pic' => '图片(单图)',
                        'pics' => '图片(多图)',
                        'files' => '文件上传',
                    ][$request->get('type')]))->set('disabled', true),
                    ...(function () use ($request, $db, $router, $field, $extra): array {
                        $res = [];
                        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', $field['adminedit'] ?? '1', [
                            '0' => '不允许',
                            '1' => '允许',
                        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
                        $res[] = (new Switchs('是否允许后台列表显示', 'adminlist', $field['adminlist'] ?? '1'))->addSwitch(
                            (new SwitchItem('不允许', 0)),
                            (new SwitchItem('允许', 1))->addItem(
                                (new Code('渲染模板', 'extra[admin_list_tpl]', $extra['admin_list_tpl'] ?? ''))->set('height', '100px'),
                            )
                        );
                        switch ($request->get('type')) {
                            case 'select':
                                $res[] = (new Select('数据源', 'extra[dict_id]', $extra['dict_id'] ?? '0', (function () use ($db): array {
                                    $res = [];
                                    foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                                        $res[] = [
                                            'title' => $vo['title'],
                                            'value' => $vo['id'],
                                        ];
                                    }
                                    return $res;
                                })()))->set('required', true)->set('help', '<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
                                $res[] = (new Radio('筛选模式', 'extra[filter_type]', $extra['filter_type'] ?? '1', [
                                    '1' => '单选',
                                    '2' => '多选',
                                ]));
                                $res[] = (new Radio('是否允许后台筛选', 'adminfilter', $field['adminfilter'] ?? '1', [
                                    '0' => '不允许',
                                    '1' => '允许',
                                ]));
                                break;

                            case 'checkbox':
                                $res[] = (new Select('数据源', 'extra[dict_id]', $extra['dict_id'] ?? '', (function () use ($db): array {
                                    $res = [];
                                    foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                                        $res[] = [
                                            'title' => $vo['title'],
                                            'value' => $vo['id'],
                                        ];
                                    }
                                    return $res;
                                })()))->set('required', true)->set('help', '<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
                                $res[] = (new Radio('筛选模式', 'extra[filter_type]', $extra['filter_type'] ?? '1', [
                                    '1' => '单选',
                                    '2' => '或多选',
                                    '3' => '且多选',
                                ]));
                                $res[] = (new Radio('是否允许后台筛选', 'adminfilter', $field['adminfilter'] ?? '1', [
                                    '0' => '不允许',
                                    '1' => '允许',
                                ]));
                                break;

                            case 'text':
                            case 'textarea':
                            case 'code':
                            case 'markdown':
                            case 'editor':
                                $res[] = (new Radio('是否作为后台的搜索字段', 'adminsearch', $field['adminsearch'] ?? '1', [
                                    '0' => '否',
                                    '1' => '是',
                                ]));
                                break;

                            case 'int':
                            case 'float':
                                $res[] = (new Radio('是否允许后台排序', 'adminorder', $field['adminorder'] ?? '1', [
                                    '0' => '不允许',
                                    '1' => '允许',
                                ]));
                                $res[] = (new Radio('是否允许负数', 'extra[negative]', $extra['negative'] ?? '0', [
                                    '0' => '不允许',
                                    '1' => '允许',
                                ]));
                                $res[] = (new Input('最小值', 'extra[min]', $extra['min'] ?? '0'));
                                $res[] = (new Input('最大值', 'extra[max]', $extra['max'] ?? '100'));
                                break;

                            case 'date':
                            case 'time':
                            case 'datetime':
                                $res[] = (new Radio('是否允许后台排序', 'adminorder', $field['adminorder'] ?? '1', [
                                    '0' => '不允许',
                                    '1' => '允许',
                                ]));
                                break;

                            default:
                                break;
                        }

                        return $res;
                    })()
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
        $extra = $request->post('extra', []);

        switch ($type) {
            case 'select':
            case 'checkbox':
                if (!$db->get('psrphp_cms_dict', '*', [
                    'id' => $extra['dict_id'] ?? 0,
                ])) {
                    return Response::error('请选择数据源');
                }
                break;

            default:
                break;
        }

        $db->insert('psrphp_cms_field', [
            'model_id' => $model['id'],
            'title' => $request->post('title'),
            'name' => $name,
            'type' => $type,
            'adminedit' => $request->post('adminedit', 0),
            'adminlist' => $request->post('adminlist', 0),
            'adminfilter' => $request->post('adminfilter', 0),
            'adminorder' => $request->post('adminorder', 0),
            'adminsearch' => $request->post('adminsearch', 0),
            'extra' => json_encode($extra, JSON_UNESCAPED_UNICODE),
        ]);
        switch ($type) {
            case 'select':
            case 'checkbox':
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` int(10) unsigned NOT NULL DEFAULT \'0\'');
                break;

            case 'text':
            case 'textarea':
            case 'pic':
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` varchar(255) NOT NULL DEFAULT \'\'');
                break;

            case 'code':
            case 'markdown':
            case 'editor':
            case 'pics':
            case 'files':
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` text');
                break;

            case 'date':
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` date');
                break;

            case 'time':
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` time');
                break;

            case 'datetime':
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` datetime');
                break;

            case 'int':
                if ($request->post('negative') == 1) {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` int(11)');
                } else {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` int(10) unsigned');
                }
                break;

            case 'float':
                if ($request->post('negative') == 1) {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` float');
                } else {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $name . '` float unsigned');
                }
                break;

            default:
                break;
        }

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }
}
