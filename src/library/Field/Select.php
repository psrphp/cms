<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
use PsrPHP\Form\Option;
use PsrPHP\Form\Select as FormSelect;
use PsrPHP\Form\SelectLevel;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;
use PsrPHP\Template\Template;

class Select implements FieldInterface
{
    public static function getTitle(): string
    {
        return '单选';
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
            $res[] = (new FormSelect('数据源', 'dict_id'))->addOption(...(function () use ($db): iterable {
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    yield new Option($vo['title'], $vo['id']);
                }
            })())->setRequired()->setHelp('<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
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
            $res[] = (new FormSelect('数据源', 'dict_id'))->addOption(...(function () use ($db, $field): iterable {
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    yield new Option($vo['title'], $vo['id'], $field['dict_id'] == $vo['id']);
                }
            })())->setRequired()->setHelp('<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
            return $res;
        });
    }

    public static function getCreateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Db $db,
        ) use ($field, $value) {
            $res = [];
            $res[] = new SelectLevel($field['title'], $field['name'], $value, (function () use ($db, $field): array {
                return $db->select('psrphp_cms_data', '*', [
                    'dict_id' => $field['dict_id'],
                    'ORDER' => [
                        'priority' => 'DESC',
                        'id' => 'ASC',
                    ],
                ]);
            })());
            return $res;
        });
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
        return Framework::execute(function (
            Db $db
        ) use ($field, $value): array {
            $res = [];
            $res[] = new SelectLevel($field['title'], $field['name'], $value, (function () use ($db, $field): array {
                return $db->select('psrphp_cms_data', '*', [
                    'dict_id' => $field['dict_id'],
                    'ORDER' => [
                        'priority' => 'DESC',
                        'id' => 'ASC',
                    ],
                ]);
            })());
            return $res;
        });
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
        return Framework::execute(function (
            Db $db
        ) use ($field, $value): array {
            $getsubval = function ($items, $val) use (&$getsubval): array {
                $res = [];
                array_push($res, $val);
                foreach ($items as $vo) {
                    if ($vo['parent'] === $val) {
                        array_push($res, ...$getsubval($items, $vo['value']));
                    }
                }
                return $res;
            };
            $datas = $db->select('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
            ]);
            $vls = [];
            foreach ((array)$value as $tmp) {
                $thisval = $db->get('psrphp_cms_data', 'value', [
                    'dict_id' => $field['dict_id'],
                    'alias' => (string)$tmp
                ]);
                if (!is_null($thisval)) {
                    array_push($vls, ...$getsubval($datas, $thisval));
                }
            }
            if ($vls) {
                return [
                    'where' => '`' . $field['name'] . '` in (' . implode(',', $vls) . ')',
                    'binds' => [],
                ];
            } else {
                return [];
            }
        });
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return Framework::execute(function (
            Db $db,
            Request $request,
            Template $template
        ) use ($field) {
            $tpl = <<<'str'
{php $_parent = []}
{foreach $pdata as $vo}
<div style="display: flex;flex-direction: row;flex-wrap: wrap;gap: 5px;">
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$_parent['alias']??''}">
        <span>不限</span>
    </label>
    {foreach $alldata as $data}
    {if $data['parent'] === $vo['parent']}
    {if $data['id'] === $vo['id']}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$data.alias}" checked>
        <span style="color: red;">{$data.title}</span>
    </label>
    {else}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$data.alias}">
        <span>{$data.title}</span>
    </label>
    {/if}
    {/if}
    {/foreach}
</div>
{php $_parent = $vo}
{/foreach}

{if $subdata}
<div style="display: flex;flex-direction: row;flex-wrap: wrap;gap: 5px;">
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$_parent['alias']??''}" checked>
        <span style="color: red;">不限</span>
    </label>
    {foreach $subdata as $sub}
    <label>
        <input type="radio" style="display: none;" name="filter[{$field.name}]" value="{$sub.alias}">
        <span>{$sub.title}</span>
    </label>
    {/foreach}
</div>
{/if}
str;
            $alldata = $db->select('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]);
            $_select = $db->get('psrphp_cms_data', '*', [
                'alias' => $request->get('filter.' . $field['name'])
            ]);
            $pdata = (function () use ($alldata, $_select) {
                $getparent = function (array $items, array $item = null) use (&$getparent): array {
                    $res = [];
                    if (!is_null($item)) {
                        foreach ($items as $vo) {
                            if ($vo['value'] === $item['parent']) {
                                array_push($res, ...$getparent($items, $vo));
                                break;
                            }
                        }
                        array_push($res, $item);
                    }
                    return $res;
                };
                return $getparent($alldata, $_select);
            })();
            $subdata = (function () use ($alldata, $_select): array {
                $parent = $_select ? $_select['value'] : null;
                $res = [];
                foreach ($alldata as $vo) {
                    if ($vo['parent'] === $parent) {
                        $res[] = $vo;
                    }
                }
                return $res;
            })();
            return $template->renderFromString($tpl, [
                'field' => $field,
                'alldata' => $alldata,
                'pdata' => $pdata,
                'subdata' => $subdata,
            ]);
        });
    }

    public static function parseToHtml(array $field, $value, array $content): string
    {
        return Framework::execute(function (
            Db $db,
            Template $template
        ) use ($field, $value) {
            $tpl = <<<'str'
<div style="display: flex;flex-direction: wrap;flex-wrap: nowrap;gap: 5px;">
    {foreach $sels as $v}
    <div>{$v['title']}</div>
    {/foreach}
</div>
str;
            $sel = $db->get('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
                'value' => $value
            ]);
            $datas = $db->select('psrphp_cms_data', '*', [
                'dict_id' => $field['dict_id'],
            ]);
            $getparent = function (array $items, array $item = null) use (&$getparent): array {
                $res = [];
                if (!is_null($item)) {
                    foreach ($items as $vo) {
                        if ($vo['value'] === $item['parent']) {
                            array_push($res, ...$getparent($items, $vo));
                            break;
                        }
                    }
                    array_push($res, $item);
                }
                return $res;
            };
            $sels = $getparent($datas, $sel);
            return $template->renderFromString($tpl, [
                'field' => $field,
                'sels' => $sels,
            ]);
        });
    }
}
