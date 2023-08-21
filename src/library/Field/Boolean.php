<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Field\Radio;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Boolean implements FieldInterface
{
    public static function getTitle(): string
    {
        return '布尔';
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
        $res[] = (new Radio('是否允许后台筛选', 'adminfilter', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }

    public static function getCreateFieldSql(string $model_name, string $field_name): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` tinyint(3) unsigned';
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
        $res[] = (new Radio('是否允许后台排序', 'adminfilter', $field['adminfilter'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }

    public static function getCreateContentForm(array $field, $value = null): array
    {
        $res = [];
        $res[] = new Radio($field['title'], $field['name'], $value ? 1 : 0, [
            0 => '否',
            1 => '是',
        ]);
        return $res;
    }

    public static function getCreateContentData(array $field): bool
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): bool {
            if ($request->has('post.' . $field['name'])) {
                return $request->post($field['name']) ? true : false;
            }
        });
    }

    public static function getUpdateContentForm(array $field, $value = null): array
    {
        $res = [];
        $res[] = new Radio($field['title'], $field['name'], $value ? 1 : 0, [
            0 => '否',
            1 => '是',
        ]);
        return $res;
    }

    public static function getUpdateContentData(array $field, $oldvalue): bool
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): bool {
            return $request->post($field['name']) ? true : false;
        });
    }

    public static function getFilterForm(array $field, $value = null): string
    {

        return Framework::execute(function (
            Template $template
        ) use ($field) {
            $tpl = <<<'str'
{if $request->get('filter.'.$field['name'], '') >=0}
<label>
    <input type="radio" style="display: none;" name="filter[{$field.name}]" value="">
    <span>不限</span>
</label>
{else}
<label>
    <input type="radio" style="display: none;" name="filter[{$field.name}]" value="" checked>
    <span style="color:red;">不限</span>
</label>
{/if}
{if $request->get('filter.'.$field['name'], '') == 1}
<label>
    <input type="radio" style="display: none;" name="filter[{$field.name}]" value="1" checked>
    <span style="color:red;">是</span>
</label>
{else}
<label>
    <input type="radio" style="display: none;" name="filter[{$field.name}]" value="1">
    <span>是</span>
</label>
{/if}
{if $request->get('filter.'.$field['name'], '') == 0}
<label>
    <input type="radio" style="display: none;" name="filter[{$field.name}]" value="0" checked>
    <span style="color:red;">否</span>
</label>
{else}
<label>
    <input type="radio" style="display: none;" name="filter[{$field.name}]" value="0">
    <span>否</span>
</label>
{/if}
str;
            return $template->renderFromString($tpl, [
                'field' => $field
            ]);
        });
    }

    public static function buildFilterSql(array $field, $value): array
    {
        if ($value == 1) {
            return [
                'where' => [
                    '`' . $field['name'] . '` = 1',
                ],
                'binds' => [],
            ];
        } elseif ($value == 0) {
            return [
                'where' => [
                    '`' . $field['name'] . '` = 0',
                ],
                'binds' => [],
            ];
        } else {
            return [];
        }
    }

    public static function parseToHtml(array $field, $value): string
    {
        if ($value) {
            return '<span>是</span>';
        } else {
            return '<span>否</span>';
        }
    }
}
