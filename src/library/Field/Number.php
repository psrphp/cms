<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Number implements FieldInterface
{
    public static function getTitle(): string
    {
        return '数字';
    }

    public static function onCreateFieldForm(): array
    {
        $res = [];
        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
        $res[] = (new Radio('是否允许后台列表显示', 'adminlist', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        $res[] = (new Radio('是否允许后台排序', 'adminorder', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        $res[] = (new Radio('是否允许负数', 'negative', '0', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '此项录入后不可更改');
        $res[] = (new Radio('是否允许小数', 'is_float', '0', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '此项录入后不可更改');
        $res[] = (new Input('最小值', 'min', null, ['type' => 'number']));
        $res[] = (new Input('最大值', 'max', null, ['type' => 'number']));
        $res[] = (new Input('数字间隔', 'step', null, ['type' => 'number']))->set('help', '若要输入小数，可填0.1、0.01、0.001等等');
        return $res;
    }

    public static function onCreateFieldData()
    {
        Framework::execute(function (
            Db $db,
            Request $request
        ) {
            $model = $db->get('psrphp_cms_model', '*', [
                'id' => $request->post('model_id'),
            ]);

            if ($request->post('is_float')) {
                if ($request->post('negative') == 1) {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` float');
                } else {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` float unsigned');
                }
            } else {
                if ($request->post('negative') == 1) {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` int(11)');
                } else {
                    $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` int(10) unsigned');
                }
            }
        });
    }

    public static function onUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', $field['adminedit'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
        $res[] = (new Radio('是否允许后台列表显示', 'adminlist', $field['adminlist'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        $res[] = (new Radio('是否允许后台排序', 'adminorder', $field['adminorder'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        $res[] = (new Input('最小值', 'min', $field['min'] ?? null, ['type' => 'number']));
        $res[] = (new Input('最大值', 'max', $field['max'] ?? null, ['type' => 'number']));
        $res[] = (new Input('数字间隔', 'step', $field['step'] ?? null, ['type' => 'number']))->set('help', '若要输入小数，可填0.1、0.01、0.001等等');
        return $res;
    }
    public static function onUpdateFieldData(): ?string
    {
        return null;
    }

    public static function onCreateContentForm(array $field, $value): array
    {
        $res = [];
        $res[] = new Input($field['title'], $field['name'], $value, [
            'type' => 'number',
            'min' => $field['min'],
            'max' => $field['max'],
            'step' => $field['step'],
        ]);
        return $res;
    }
    public static function onCreateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): ?string {
            if ($request->has('post.' . $field['name'])) {
                return $request->post($field['name']);
            }
        });
    }
    public static function onUpdateContentForm(array $field, $value): array
    {
        $res = [];
        $res[] = new Input($field['title'], $field['name'], $value, [
            'type' => 'number',
            'min' => $field['min'],
            'max' => $field['max'],
            'step' => $field['step'],
        ]);
        return $res;
    }
    public static function onUpdateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field) {
            return $request->post($field['name']);
        });
    }

    public static function onContentFilter(array $field, $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        if (!isset($value['min'])) {
            $value['min'] = '';
        }
        if (!isset($value['max'])) {
            $value['max'] = '';
        }
        $minkey = ':minkey_' . $field['name'];
        $maxkey = ':maxkey_' . $field['name'];
        if (strlen($value['min']) && strlen($value['max'])) {
            return [
                'sql' => '`' . $field['name'] . '` BETWEEN ' . $minkey . ' AND ' . $maxkey,
                'binds' => [
                    $minkey => $value['min'],
                    $maxkey => $value['max'],
                ]
            ];
        } elseif (strlen($value['min'])) {
            return [
                'sql' => '`' . $field['name'] . '`>=' . $minkey,
                'binds' => [
                    $minkey => $value['min'],
                ]
            ];
        } elseif (strlen($value['max'])) {
            return [
                'sql' => '`' . $field['name'] . '`<=' . $maxkey,
                'binds' => [
                    $maxkey => $value['max'],
                ]
            ];
        }
        return [];
    }

    public static function onContentSearch(array $field, string $value): array
    {
        return [];
    }

    public static function onFilter(array $field): string
    {
        return Framework::execute(function (
            Template $template
        ) use ($field) {
            $tpl = <<<'str'
<div class="d-flex gap-1">
    <div>
        <input type="number" name="filter[{$field['name']}][min]" value="{$request->get('filter.'.$field['name'].'.min')}" class="form-control form-control-sm">
    </div>
    <div>
        <input type="number" name="filter[{$field['name']}][max]" value="{$request->get('filter.'.$field['name'].'.max')}" class="form-control form-control-sm">
    </div>
</div>
str;
            return $template->renderFromString($tpl, [
                'field' => $field
            ]);
        });
    }

    public static function onShow(array $field, $value): string
    {
        return (string)$value;
    }
}
