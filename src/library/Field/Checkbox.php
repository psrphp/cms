<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
use PsrPHP\Form\Field\Checkbox as FieldCheckbox;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Form\Field\Select;
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
            $res[] = (new Select('数据源', 'dict_id', '', (function () use ($db): array {
                $res = [];
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    $res[] = [
                        'title' => $vo['title'],
                        'value' => $vo['id'],
                    ];
                }
                return $res;
            })()))->set('required', true)->set('help', '<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
            $res[] = (new Radio('筛选类型', 'filtertype', '0', [
                '0' => '单选',
                '1' => '多选(或)',
                '2' => '多选(且)',
            ]));
            return $res;
        });
    }

    public static function getCreateFieldSql(string $model_name, string $field_name): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` int(10) unsigned NOT NULL DEFAULT \'0\'';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        return Framework::execute(function (
            Db $db,
            Router $router
        ) use ($field) {
            $res = [];
            $res[] = (new Select('数据源', 'dict_id', $field['dict_id'] ?? '', (function () use ($db): array {
                $res = [];
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    $res[] = [
                        'title' => $vo['title'],
                        'value' => $vo['id'],
                    ];
                }
                return $res;
            })()))->set('required', true)->set('help', '<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');

            $res[] = (new Radio('筛选类型', 'filtertype', $field['filtertype'] ?? '0', [
                '0' => '单选',
                '1' => '多选(或)',
                '2' => '多选(且)',
            ]));
            return $res;
        });
    }

    public static function getCreateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Db $db,
        ) use ($field, $value): array {
            $res = [];
            $vals = [];
            for ($i = 0; $i < 32; $i++) {
                $pow = pow(2, $i);
                if (($value & $pow) == $pow) {
                    $vals[] = $i;
                }
            }
            $res[] = new FieldCheckbox($field['title'], $field['name'], $vals, (function () use ($db, $field): array {
                $res = [];
                foreach ($db->select('psrphp_cms_data', '*', [
                    'dict_id' => $field['dict_id'],
                    'parent' => null,
                    'ORDER' => [
                        'priority' => 'DESC',
                        'id' => 'ASC',
                    ],
                ]) as $data) {
                    $res[$data['value']] = $data['title'];
                }
                return $res;
            })());
            return $res;
        });
    }

    public static function getCreateContentData(array $field): int
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): int {
            $res = 0;
            foreach ($request->post($field['name'], []) as $v) {
                $res += pow(2, $v);
            }
            return $res;
        });
    }

    public static function getUpdateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Db $db
        ) use ($field, $value): array {
            $res = [];
            $vals = [];
            for ($i = 0; $i < 32; $i++) {
                $pow = pow(2, $i);
                if (($value & $pow) == $pow) {
                    $vals[] = $i;
                }
            }
            $res[] = new FieldCheckbox($field['title'], $field['name'], $vals, (function () use ($db, $field): array {
                $res = [];
                foreach ($db->select('psrphp_cms_data', '*', [
                    'dict_id' => $field['dict_id'],
                    'parent' => null,
                    'ORDER' => [
                        'priority' => 'DESC',
                        'id' => 'ASC',
                    ],
                ]) as $data) {
                    $res[$data['value']] = $data['title'];
                }
                return $res;
            })());
            return $res;
        });
    }

    public static function getUpdateContentData(array $field, $oldvalue): int
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): int {
            $res = 0;
            foreach ($request->post($field['name'], []) as $v) {
                $res += pow(2, $v);
            }
            return $res;
        });
    }

    public static function buildFilterSql(array $field, $alias): array
    {
        return Framework::execute(function (
            Db $db
        ) use ($field, $alias): array {
            switch ($field['filtertype']) {
                case '0':
                    if (is_string($alias) && strlen($alias)) {
                        $value = $db->get('psrphp_cms_data', 'value', [
                            'dict_id' => $field['dict_id'],
                            'alias' => $alias
                        ]);
                        if (!is_null($value)) {
                            $x = pow(2, $value);
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
                    if ($alias && is_array($alias)) {
                        $x = 0;
                        foreach ($db->select('psrphp_cms_data', 'value', [
                            'dict_id' => $field['dict_id'],
                            'alias' => $alias
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
                    if ($alias && is_array($alias)) {
                        $x = 0;
                        foreach ($db->select('psrphp_cms_data', 'value', [
                            'dict_id' => $field['dict_id'],
                            'alias' => $alias
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

    public static function parseToHtml(array $field, $value): string
    {
        return Framework::execute(function (
            Db $db,
            Template $template
        ) use ($field, $value) {
            $datas = $db->select('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]);
            $sels = [];
            $strs = array_reverse(str_split(decbin($value) . ''));
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
