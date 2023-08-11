<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
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
        $res[] = (new Radio('是否允许后台筛选', 'adminfilter', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
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
            $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` tinyint(3) unsigned');
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
        $res[] = (new Radio('是否允许后台排序', 'adminfilter', $field['adminfilter'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }
    public static function onUpdateFieldData(): ?string
    {
        return null;
    }

    public static function onCreateContentForm(array $field, $value): array
    {
        $res = [];
        $res[] = new Radio($field['title'], $field['name'], $value, [
            0 => '否',
            1 => '是',
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
        $res[] = new Radio($field['title'], $field['name'], $value, [
            0 => '否',
            1 => '是',
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
        if ($value == 1) {
            return [
                'sql' => '`' . $field['name'] . '` = 1',
                'binds' => [],
            ];
        } elseif ($value == 0) {
            return [
                'sql' => '`' . $field['name'] . '` = 0',
                'binds' => [],
            ];
        } else {
            return [];
        }
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
{if $request->get('filter.'.$field['name'], '') >=0}
<label onclick="$(this).siblings('input').removeAttr('checked')">
    <span class="badge text-bg-light text-secondary">不限</span>
    <input type="radio" class="d-none" name="filter[{$field.name}]" value="" autocomplete="off">
</label>
{else}
<label onclick="$(this).siblings('input').removeAttr('checked')">
    <span class="badge text-bg-secondary">不限</span>
    <input type="radio" class="d-none" name="filter[{$field.name}]" value="" autocomplete="off" checked>
</label>
{/if}
{if $request->get('filter.'.$field['name'], '') == 1}
<label onclick="$(this).siblings('input').removeAttr('checked')">
    <span class="badge text-bg-secondary">是</span>
    <input type="radio" class="d-none" name="filter[{$field.name}]" value="1" autocomplete="off" checked>
</label>
{else}
<label onclick="$(this).siblings('input').removeAttr('checked')">
    <span class="badge text-bg-light text-secondary">是</span>
    <input type="radio" class="d-none" name="filter[{$field.name}]" value="1" autocomplete="off">
</label>
{/if}
{if $request->get('filter.'.$field['name'], '') == 0}
<label onclick="$(this).siblings('input').removeAttr('checked')">
    <span class="badge text-bg-secondary">否</span>
    <input type="radio" class="d-none" name="filter[{$field.name}]" value="0" autocomplete="off" checked>
</label>
{else}
<label onclick="$(this).siblings('input').removeAttr('checked')">
    <span class="badge text-bg-light text-secondary">否</span>
    <input type="radio" class="d-none" name="filter[{$field.name}]" value="0" autocomplete="off">
</label>
{/if}
str;
            return $template->renderFromString($tpl, [
                'field' => $field
            ]);
        });
    }

    public static function onShow(array $field, $value): string
    {
        if ($value) {
            return '<span>是</span>';
        } else {
            return '<span>否</span>';
        }
    }
}
