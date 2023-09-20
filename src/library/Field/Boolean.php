<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Radio;
use PsrPHP\Form\Radios;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Boolean implements FieldInterface
{
    public static function getTitle(): string
    {
        return '布尔';
    }

    public static function isOrderable(): bool
    {
        return false;
    }

    public static function isSearchable(): bool
    {
        return false;
    }

    public static function getCreateFieldForm(): array
    {
        return [];
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` tinyint(3) unsigned';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        return [];
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new Radios($field['title']))->addRadio(
            new Radio('否', $field['name'], 0, ($content[$field['name']] ?? $field['default'] ?? 0) == 0),
            new Radio('是', $field['name'], 1, ($content[$field['name']] ?? $field['default'] ?? 0) == 1),
        )->setHelp($field['tips'] ?? '');
        return $res;
    }

    public static function getCreateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name']) ? true : false;
        });
    }

    public static function getUpdateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new Radios($field['title']))->addRadio(
            new Radio('否', $field['name'], 0, ($content[$field['name']] ?? $field['default'] ?? 0) == 0),
            new Radio('是', $field['name'], 1, ($content[$field['name']] ?? $field['default'] ?? 0) == 1),
        )->setHelp($field['tips'] ?? '');
        return $res;
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name']) ? true : false;
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
                'where' => '`' . $field['name'] . '` = 1',
                'binds' => [],
            ];
        } elseif ($value == 0) {
            return [
                'where' => '`' . $field['name'] . '` = 0',
                'binds' => [],
            ];
        } else {
            return [];
        }
    }

    public static function parseToHtml(array $field, array $content): string
    {
        if ($content[$field['name']]) {
            return '<span>是</span>';
        } else {
            return '<span>否</span>';
        }
    }
}
