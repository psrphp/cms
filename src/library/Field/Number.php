<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Input;
use PsrPHP\Form\Radio;
use PsrPHP\Form\Radios;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Number implements FieldInterface
{
    public static function getTitle(): string
    {
        return '数字';
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
        $res[] = (new Radios('是否允许负数'))->addRadio(
            new Radio('不允许', 'is_negative', 0, true),
            new Radio('允许', 'is_negative', 1, false),
        )->setHelp('此项录入后不可更改');
        $res[] = (new Radios('是否允许小数'))->addRadio(
            new Radio('不允许', 'is_float', 0, true),
            new Radio('允许', 'is_float', 1, false),
        )->setHelp('此项录入后不可更改');
        $res[] = (new Input('最小值', 'min', null, 'number'));
        $res[] = (new Input('最大值', 'max', null, 'number'));
        $res[] = (new Input('数字间隔', 'step', null, 'number'))->setHelp('若要输入小数，可填0.1、0.01、0.001等等');
        return $res;
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        $is_float = isset($_POST['is_float']) && $_POST['is_float'];
        $is_negative = isset($_POST['is_negative']) && $_POST['is_negative'];
        if ($is_float) {
            if ($is_negative) {
                return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` float';
            } else {
                return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` float unsigned';
            }
        } else {
            if ($is_negative) {
                return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` int(11)';
            } else {
                return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` int(10) unsigned';
            }
        }
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Input('最小值', 'min', $field['min'] ?? null, 'number'));
        $res[] = (new Input('最大值', 'max', $field['max'] ?? null, 'number'));
        $res[] = (new Input('数字间隔', 'step', $field['step'] ?? null, 'number'))->setHelp('若要输入小数，可填0.1、0.01、0.001等等');
        return $res;
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $res = [];
        $tmp = (new Input($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? '', 'number'))->setHelp($field['tips'] ?? '');
        if (isset($field['min']) && is_numeric($field['min'])) {
            $tmp->setMin($field['min']);
        }
        if (isset($field['max']) && is_numeric($field['max'])) {
            $tmp->setMax($field['max']);
        }
        if (isset($field['step']) && is_numeric($field['step'])) {
            $tmp->setStep($field['step']);
        }
        $res[] = $tmp;
        return $res;
    }

    public static function getCreateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name']);
        });
    }

    public static function getUpdateContentForm(array $field, array $content): array
    {
        $res = [];
        $tmp = (new Input($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? '', 'number'))->setHelp($field['tips'] ?? '');
        if (isset($field['min']) && is_numeric($field['min'])) {
            $tmp->setMin($field['min']);
        }
        if (isset($field['max']) && is_numeric($field['max'])) {
            $tmp->setMax($field['max']);
        }
        if (isset($field['step']) && is_numeric($field['step'])) {
            $tmp->setStep($field['step']);
        }
        $res[] = $tmp;
        return $res;
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name']);
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
                'where' =>  '`' . $field['name'] . '` BETWEEN ' . $minkey . ' AND ' . $maxkey,
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

    public static function parseToHtml(array $field, array $content): null|int|float
    {
        return $content[$field['name']];
    }
}
