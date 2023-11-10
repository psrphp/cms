<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Framework\Framework;
use PsrPHP\Template\Template;

class Updatetime implements FieldInterface
{
    public static function getTitle(): string
    {
        return '更新时间';
    }

    public static function isOrderable(): bool
    {
        return true;
    }

    public static function isSearchable(): bool
    {
        return false;
    }

    public static function getCreateFieldForm(): array
    {
        $res = [];
        return $res;
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        return $res;
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $res = [];
        return $res;
    }
    public static function getCreateContentData(array $field, array &$content)
    {
        $content[$field['name']] = date('Y-m-d H:i:s');
    }
    public static function getUpdateContentForm(array $field, array $content): array
    {
        $res = [];
        return $res;
    }
    public static function getUpdateContentData(array $field, array &$content)
    {
        $content[$field['name']] =  date('Y-m-d H:i:s');
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return Framework::execute(function (
            Template $template
        ) use ($field) {
            $tpl = <<<'str'
<div style="display: flex;flex-direction: column;gap: 5px;">
    <div>
        <input type="datetime-local" name="filter[{$field['name']}][min]" value="{$request->get('filter.'.$field['name'].'.min')}">
    </div>
    <div>
        <input type="datetime-local" name="filter[{$field['name']}][max]" value="{$request->get('filter.'.$field['name'].'.max')}">
    </div>
</div>
str;
            return $template->renderFromString($tpl, [
                'field' => $field
            ]);
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
                'where' => '`' . $field['name'] . '` BETWEEN ' . $minkey . ' AND ' . $maxkey,
                'binds' => [
                    $minkey => $value['min'],
                    $maxkey => $value['max'],
                ]
            ];
        } elseif (strlen($value['min'])) {
            return [
                'where' => '`' . $field['name'] . '`>=' . $minkey,
                'binds' => [
                    $minkey => $value['min'],
                ]
            ];
        } elseif (strlen($value['max'])) {
            return [
                'where' => '`' . $field['name'] . '`<=' . $maxkey,
                'binds' => [
                    $maxkey => $value['max'],
                ]
            ];
        }
        return [];
    }

    public static function parseToHtml(array $field, array $content): ?string
    {
        return $content[$field['name']];
    }
}
