<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Field\Radio;
use PsrPHP\Framework\Framework;
use PsrPHP\Template\Template;

class Updatetime implements FieldInterface
{
    public static function getTitle(): string
    {
        return '更新时间';
    }

    public static function getCreateFieldForm(): array
    {
        $res = [];
        $res[] = (new Radio('是否允许后台排序', 'adminorder', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }

    public static function getCreateFieldSql(string $model_name, string $field_name): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Radio('是否允许后台排序', 'adminorder', $field['adminorder'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }

    public static function getCreateContentForm(array $field, $value = null): array
    {
        $res = [];
        return $res;
    }
    public static function getCreateContentData(array $field): string
    {
        return date('Y-m-d H:i:s');
    }
    public static function getUpdateContentForm(array $field, $value = null): array
    {
        $res = [];
        return $res;
    }
    public static function getUpdateContentData(array $field, $oldvalue): string
    {
        return date('Y-m-d H:i:s');
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

    public static function parseToHtml(array $field, $value): string
    {
        return '' . $value;
    }
}
