<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

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

    public static function getCreateFieldForm(): array
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
        $res[] = (new Radio('是否允许负数', 'is_negative', '0', [
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

    public static function getCreateFieldSql(string $model_name, string $field_name): string
    {
        $is_float = isset($_POST['is_float']) && $_POST['is_float'];
        $is_negative = isset($_POST['is_negative']) && $_POST['is_negative'];
        if ($is_float) {
            if ($is_negative) {
                return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` float';
            } else {
                return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` float unsigned';
            }
        } else {
            if ($is_negative) {
                return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` int(11)';
            } else {
                return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` int(10) unsigned';
            }
        }
    }

    public static function getUpdateFieldForm(array $field): array
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

    public static function getCreateContentForm(array $field, $value = null): array
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

    public static function getCreateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): ?string {
            if ($request->has('post.' . $field['name'])) {
                return $request->post($field['name']);
            }
        });
    }

    public static function getUpdateContentForm(array $field, $value = null): array
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

    public static function getUpdateContentData(array $field, $oldvalue): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field) {
            return $request->post($field['name']);
        });
    }

    public static function buildFilterSql(array $field, $value): array
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
                'where' => [
                    '`' . $field['name'] . '` BETWEEN ' . $minkey . ' AND ' . $maxkey,
                ],
                'binds' => [
                    $minkey => $value['min'],
                    $maxkey => $value['max'],
                ]
            ];
        } elseif (strlen($value['min'])) {
            return [
                'where' => [
                    '`' . $field['name'] . '`>=' . $minkey,
                ],
                'binds' => [
                    $minkey => $value['min'],
                ]
            ];
        } elseif (strlen($value['max'])) {
            return [
                'where' => [
                    '`' . $field['name'] . '`<=' . $maxkey,
                ],
                'binds' => [
                    $maxkey => $value['max'],
                ]
            ];
        }
        return [];
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return Framework::execute(function (
            Template $template
        ) use ($field) {
            $tpl = <<<'str'
<div style="display: flex;flex-direction: column;gap: 5px;">
    <div>
        <input type="number" name="filter[{$field['name']}][min]" value="{$request->get('filter.'.$field['name'].'.min')}">
    </div>
    <div>
        <input type="number" name="filter[{$field['name']}][max]" value="{$request->get('filter.'.$field['name'].'.max')}">
    </div>
</div>
str;
            return $template->renderFromString($tpl, [
                'field' => $field
            ]);
        });
    }

    public static function parseToHtml(array $field, $value): string
    {
        return (string)$value;
    }
}
