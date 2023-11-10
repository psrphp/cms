<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Checkbox as FormCheckbox;
use PsrPHP\Form\Checkboxs;
use PsrPHP\Form\Radio;
use PsrPHP\Form\Radios;
use PsrPHP\Form\Textarea;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Checkbox implements FieldInterface
{
    public static function getTitle(): string
    {
        return '多选';
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
        $res = [];
        $res[] = (new Textarea('选项', 'items'))->setRequired()->setHelp('一行一个，格式：标题|值|父级值');
        $res[] = (new Radios('筛选类型'))->addRadio(
            new Radio('单选', 'filtertype', 0, true),
            new Radio('多选(或)', 'filtertype', 1, false),
            new Radio('多选(且)', 'filtertype', 2, false),
        );
        return $res;
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` varchar(255) NOT NULL DEFAULT \'\'';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Textarea('选项', 'items', $field['items']))->setRequired()->setHelp('一行一个，格式：标题|值|父级值');
        $res[] = (new Radios('筛选类型'))->addRadio(
            new Radio('单选', 'filtertype', 0, $field['filtertype'] == 0),
            new Radio('多选(或)', 'filtertype', 1, $field['filtertype'] == 1),
            new Radio('多选(且)', 'filtertype', 2, $field['filtertype'] == 2),
        );
        return $res;
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new Checkboxs($field['title']))->addCheckbox(...(function () use ($field, $content): iterable {
            $vals = array_filter(explode('|', $content[$field['name']] ?? ''));
            foreach (array_filter(explode("\r\n", $field['items'])) as $vo) {
                $tmp = explode('|', $vo . '||||');
                yield new FormCheckbox($tmp[0], $field['name'] . '[]', $tmp[1], in_array($tmp[1], $vals));
            }
        })())->setHelp($field['tips'] ?? '');
        return $res;
    }

    public static function getCreateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = '|' . implode('|', $request->post($field['name'], [])) . '|';
        });
    }

    public static function getUpdateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new Checkboxs($field['title']))->addCheckbox(...(function () use ($field, $content): iterable {
            $vals = array_filter(explode('|', $content[$field['name']] ?? ''));
            foreach (array_filter(explode("\r\n", $field['items'])) as $vo) {
                $tmp = explode('|', $vo . '||||');
                yield new FormCheckbox($tmp[0], $field['name'] . '[]', $tmp[1], in_array($tmp[1], $vals));
            }
        })())->setHelp($field['tips'] ?? '');
        return $res;
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = '|' . implode('|', $request->post($field['name'], [])) . '|';
        });
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return Framework::execute(function (
            Template $template
        ) use ($field): string {
            switch ($field['filtertype']) {
                case '0':
                    $tpl = <<<'str'
<div>
    {if $request->get('filter.'.$field['name'])}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="">
        <span>不限</span>
    </label>
    {else}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="" checked>
        <span style="color: red;">不限</span>
    </label>
    {/if}
    {foreach $items as $vo}
    {if $vo['value'] === $request->get('filter.'.$field['name'])}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$vo.value}" checked>
        <span style="color: red;">{$vo.title}</span>
    </label>
    {else}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$vo.value}">
        <span>{$vo.title}</span>
    </label>
    {/if}
    {/foreach}
</div>
str;
                    break;

                case '1':
                case '2':
                    $tpl = <<<'str'
<div>
    {foreach $items as $vo}
    {if in_array($vo['value'], (array)$request->get('filter.'.$field['name']))}
    <label>
        <input type="checkbox" style="display: none;" name="filter[{$field.name}][]" value="{$vo.value}" autocomplete="off" checked>
        <span style="color: red;">{$vo.title}</span>
    </label>
    {else}
    <label>
        <input type="checkbox" style="display: none;" name="filter[{$field.name}][]" value="{$vo.value}" autocomplete="off">
        <span>{$vo.title}</span>
    </label>
    {/if}
    {/foreach}
</div>
str;
                    break;

                default:
                    $tpl = '';
                    break;
            }

            $items = [];
            foreach (array_filter(explode("\r\n", $field['items'])) as $vo) {
                $tmp = explode('|', $vo . '||||');
                $items[] = [
                    'title' => $tmp[0],
                    'value' => $tmp[1],
                    'parent' => $tmp[2],
                    'disabled' => $tmp[3] ? true : false,
                    'group' => $tmp[4],
                ];
            }
            return $template->renderFromString($tpl, [
                'field' => $field,
                'items' => $items,
            ]);
        });
    }

    public static function buildFilterSql(array $field, $value): array
    {
        switch ($field['filtertype']) {
            case '0':
                if (is_string($value) && strlen($value)) {
                    return [
                        'where' =>  '`' . $field['name'] . '` like \'%|' . addslashes($value) . '|%\'',
                        'binds' => []
                    ];
                }
                break;

            case '1':
                if ($value && is_array($value)) {

                    $tmps = [];
                    foreach ($value as $vo) {
                        $tmps[] = '`' . $field['name'] . '` like \'%|' . addslashes($vo) . '|%\'';
                    }

                    if ($tmps) {
                        return [
                            'where' => '(' . implode(' or ', $tmps) . ')',
                            'binds' => []
                        ];
                    }
                }
                break;

            case '2':
                if ($value && is_array($value)) {
                    $tmps = [];
                    foreach ($value as $vo) {
                        $tmps[] = '`' . $field['name'] . '` like \'%|' . addslashes($vo) . '|%\'';
                    }

                    if ($tmps) {
                        return [
                            'where' => '(' . implode(' and ', $tmps) . ')',
                            'binds' => []
                        ];
                    }
                }
                break;

            default:
                return [];
                break;
        }
        return [];
    }

    public static function parseToHtml(array $field, array $content): string
    {
        if (!isset($content[$field['name']])) {
            return '';
        }
        $values = explode('|', $content[$field['name']]);

        $selected = [];
        foreach (array_filter(explode("\r\n", $field['items'])) as $vo) {
            $tmp = explode('|', $vo . '|');
            if (in_array($tmp[1], $values)) {
                $selected[] = $tmp[0];
            }
        }

        return implode(',', $selected);
    }
}
