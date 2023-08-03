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

class Update extends Common
{
    public function get(
        Db $db,
        Router $router,
        Request $request,
    ) {
        $field = $db->get('psrphp_cms_field', '*', [
            'id' => $request->get('id'),
        ]);
        $extra = json_decode($field['extra'], true);
        $form = new Builder('编辑字段');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-8'))->addItem(
                    (new Hidden('id', $field['id'])),
                    (new Input('标题', 'title', $field['title']))->set('help', '例如：'),
                    (new Input('字段', 'name', $field['name']))->set('disabled', true),
                    (new Input('类型', 'typedisabled', [
                        'select' => '单选',
                        'checkbox' => '多选',
                        'text' => '单行文本',
                        'textarea' => '多行文本',
                        'pic' => '图片(单图)',
                        'pics' => '图片(多图)',
                        'code' => '代码编辑器',
                        'markdown' => 'markdown编辑器',
                        'editor' => '富文本编辑器',
                        'int' => '整数',
                        'float' => '浮点数',
                        'time' => '时间',
                        'date' => '日期',
                        'datetime' => '日期时间',
                    ][$field['type']]))->set('disabled', true),
                    ...(function () use ($db, $router, $field, $extra): array {
                        $res = [];
                        $res[] = (new Radio('是否允许通过表单编辑', 'editable', $field['editable'] ?? '1', [
                            '0' => '不允许',
                            '1' => '允许',
                        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
                        $res[] = (new Switchs('是否允许后台列表显示', 'listable', $field['listable'] ?? '1'))->addSwitch(
                            (new SwitchItem('不允许', 0)),
                            (new SwitchItem('允许', 1))->addItem(
                                (new Code('渲染模板', 'extra[list_code]', $extra['list_code'] ?? ''))->set('height', '100px'),
                            )
                        );
                        switch ($field['type']) {
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
                                break;

                            case 'text':
                            case 'textarea':
                            case 'code':
                            case 'markdown':
                            case 'editor':
                                break;

                            case 'int':
                            case 'float':
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
        Request $request,
    ) {
        $field = $db->get('psrphp_cms_field', '*', [
            'id' => $request->post('id'),
        ]);

        $update = array_intersect_key($request->post(), [
            'title' => '',
            'editable' => '',
            'listable' => '',
        ]);

        if ($extra = $request->post('extra', [])) {
            $update['extra'] = json_encode(array_merge(json_decode($field['extra'], true), $extra), JSON_UNESCAPED_UNICODE);
        }

        $db->update('psrphp_cms_field', $update, [
            'id' => $field['id'],
        ]);

        return Response::success('操作成功！');
    }
}
