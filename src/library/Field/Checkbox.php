<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
use PsrPHP\Form\Checkbox as FormCheckbox;
use PsrPHP\Form\Checkboxs;
use PsrPHP\Form\Option;
use PsrPHP\Form\Radio;
use PsrPHP\Form\Radios;
use PsrPHP\Form\Select;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;
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
        return Framework::execute(function (
            Db $db,
            Router $router
        ): array {
            $res = [];
            $res[] = (new Select('数据源', 'dict_id'))->addOption(...(function () use ($db): iterable {
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    yield new Option($vo['title'], $vo['id']);
                }
            })())->setRequired()->setHelp('<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
            $res[] = (new Radios('筛选类型'))->addRadio(
                new Radio('单选', 'filtertype', 0, true),
                new Radio('多选(或)', 'filtertype', 1, false),
                new Radio('多选(且)', 'filtertype', 2, false),
            );
            return $res;
        });
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` int(10) unsigned NOT NULL DEFAULT \'0\'';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        return Framework::execute(function (
            Db $db,
            Router $router
        ) use ($field) {
            $res = [];
            $res[] = (new Select('数据源', 'dict_id'))->addOption(...(function () use ($db, $field): iterable {
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    yield new Option($vo['title'], $vo['id'], $field['dict_id'] == $vo['id']);
                }
            })())->setRequired()->setHelp('<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
            $res[] = (new Radios('筛选类型'))->addRadio(
                new Radio('单选', 'filtertype', 0, $field['filtertype'] == 0),
                new Radio('多选(或)', 'filtertype', 1, $field['filtertype'] == 1),
                new Radio('多选(且)', 'filtertype', 2, $field['filtertype'] == 1),
            );
            return $res;
        });
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $value = $content[$field['name']] ?? 0;
        return Framework::execute(function (
            Db $db,
        ) use ($field, $value): array {
            $res = [];
            $res[] = (new Checkboxs($field['title']))->addCheckbox(...(function () use ($value, $db, $field): iterable {
                $vals = [];
                for ($i = 0; $i < 32; $i++) {
                    $pow = pow(2, $i);
                    if (($value & $pow) == $pow) {
                        $vals[] = $i;
                    }
                }
                foreach ($db->select('psrphp_cms_data', '*', [
                    'dict_id' => $field['dict_id'],
                    'parent' => null,
                    'ORDER' => [
                        'priority' => 'DESC',
                        'id' => 'ASC',
                    ],
                ]) as $vo) {
                    yield new FormCheckbox($vo['title'], $field['name'], $vo['value'], in_array($vo['value'], $vals));
                }
            })())->setHelp($field['tips'] ?? '');
            return $res;
        });
    }

    public static function getCreateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $res = 0;
            foreach ($request->post($field['name'], []) as $v) {
                $res += pow(2, $v);
            }
            $content[$field['name']] = $res;
        });
    }

    public static function getUpdateContentForm(array $field, array $content): array
    {
        $value = $content[$field['name']] ?? 0;
        return Framework::execute(function (
            Db $db
        ) use ($field, $value): array {
            $res = [];
            $res[] = (new Checkboxs($field['title']))->addCheckbox(...(function () use ($value, $field, $db): iterable {
                $vals = [];
                for ($i = 0; $i < 32; $i++) {
                    $pow = pow(2, $i);
                    if (($value & $pow) == $pow) {
                        $vals[] = $i;
                    }
                }
                foreach ($db->select('psrphp_cms_data', '*', [
                    'dict_id' => $field['dict_id'],
                    'parent' => null,
                    'ORDER' => [
                        'priority' => 'DESC',
                        'id' => 'ASC',
                    ],
                ]) as $vo) {
                    yield new FormCheckbox($vo['title'], $field['name'], $vo['value'], in_array($vo['value'], $vals));
                }
            })())->setHelp($field['tips'] ?? '');
            return $res;
        });
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $res = 0;
            foreach ($request->post($field['name'], []) as $v) {
                $res += pow(2, $v);
            }
            $content[$field['name']] = $res;
        });
    }

    public static function buildFilterSql(array $field, $value): array
    {
        return Framework::execute(function (
            Db $db
        ) use ($field, $value): array {
            switch ($field['filtertype']) {
                case '0':
                    if (is_string($value) && strlen($value)) {
                        $vo = $db->get('psrphp_cms_data', 'value', [
                            'dict_id' => $field['dict_id'],
                            'alias' => $value
                        ]);
                        if (!is_null($vo)) {
                            $x = pow(2, $vo);
                            return [
                                'where' =>  '`' . $field['name'] . '` & ' . $x . ' > 0',
                                'binds' => []
                            ];
                        } else {
                            return [];
                        }
                    }
                    break;

                case '1':
                    if ($value && is_array($value)) {
                        $x = 0;
                        foreach ($db->select('psrphp_cms_data', 'value', [
                            'dict_id' => $field['dict_id'],
                            'alias' => $value
                        ]) as $vl) {
                            $x += pow(2, $vl);
                        }
                        if ($x) {
                            return [
                                'where' => '`' . $field['name'] . '` & ' . $x . ' > 0',
                                'binds' => []
                            ];
                        } else {
                            return [];
                        }
                    }
                    break;

                case '2':
                    if ($value && is_array($value)) {
                        $x = 0;
                        foreach ($db->select('psrphp_cms_data', 'value', [
                            'dict_id' => $field['dict_id'],
                            'alias' => $value
                        ]) as $vl) {
                            $x += pow(2, $vl);
                        }
                        if ($x) {
                            return [
                                'where' => '`' . $field['name'] . '` & ' . $x . ' = ' . $x,
                                'binds' => []
                            ];
                        } else {
                            return [];
                        }
                    }
                    break;

                default:
                    return [];
                    break;
            }
            return [];
        });
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return Framework::execute(function (
            Db $db,
            Template $template
        ) use ($field): string {
            $alldata = $db->select('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]);
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
    {foreach $alldata as $vo}
    {if $vo['parent'] === null}
    {if $vo['alias'] === $request->get('filter.'.$field['name'])}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$vo.alias}" checked>
        <span style="color: red;">{$vo.title}</span>
    </label>
    {else}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$vo.alias}">
        <span>{$vo.title}</span>
    </label>
    {/if}
    {/if}
    {/foreach}
</div>
str;
                    break;

                case '1':
                case '2':
                    $tpl = <<<'str'
<div>
    {foreach $alldata as $vo}
    {if $vo['parent'] === null}
    {if in_array($vo['alias'], (array)$request->get('filter.'.$field['name']))}
    <label>
        <input type="checkbox" style="display: none;" name="filter[{$field.name}][]" value="{$vo.alias}" autocomplete="off" checked>
        <span style="color: red;">{$vo.title}</span>
    </label>
    {else}
    <label>
        <input type="checkbox" style="display: none;" name="filter[{$field.name}][]" value="{$vo.alias}" autocomplete="off">
        <span>{$vo.title}</span>
    </label>
    {/if}
    {/if}
    {/foreach}
</div>
str;
                    break;

                default:
                    $tpl = '';
                    break;
            }
            return $template->renderFromString($tpl, [
                'field' => $field,
                'alldata' => $alldata,
            ]);
        });
    }

    public static function parseToHtml(array $field, array $content): string
    {
        return Framework::execute(function (
            Db $db,
            Template $template
        ) use ($field, $content) {
            $datas = $db->select('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]);
            $sels = [];
            $strs = array_reverse(str_split(decbin($content[$field['name']]) . ''));
            foreach ($strs as $key => $vo) {
                if (!$vo) {
                    continue;
                }
                foreach ($datas as $v) {
                    if ($v['value'] === intval($key)) {
                        $sels[] = $v;
                    }
                }
            }

            $tpl = <<<'str'
<div style="display: flex;flex-wrap: wrap;gap: 5px;">
    {foreach $sels as $v}
    <div>{$v['title']}</div>
    {/foreach}
</div>
str;
            return $template->renderFromString($tpl, [
                'field' => $field,
                'sels' => $sels,
            ]);
        });
    }
}
