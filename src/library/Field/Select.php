<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Form\Field\Select as FieldSelect;
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

    public static function onCreateFieldForm(): array
    {
        return Framework::execute(function (
            Db $db,
            Router $router
        ): array {
            $res = [];
            $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', '1', [
                '0' => '不允许',
                '1' => '允许',
            ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
            $res[] = (new Radio('是否允许后台列表显示', 'adminlist', '1', [
                '0' => '不允许',
                '1' => '允许',
            ]));
            $res[] = (new FieldSelect('数据源', 'dict_id', '', (function () use ($db): array {
                $res = [];
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    $res[] = [
                        'title' => $vo['title'],
                        'value' => $vo['id'],
                    ];
                }
                return $res;
            })()))->set('required', true)->set('help', '<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
            $res[] = (new Radio('是否允许后台筛选', 'adminfilter', '1', [
                '0' => '不允许',
                '1' => '允许',
            ]));
            return $res;
        });
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
            $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` int(10) unsigned NOT NULL DEFAULT \'0\'');
        });
    }

    public static function onUpdateFieldForm(array $field): array
    {
        return Framework::execute(function (
            Db $db,
            Router $router
        ) use ($field) {
            $res = [];
            $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', $field['adminedit'] ?? '1', [
                '0' => '不允许',
                '1' => '允许',
            ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
            $res[] = (new Radio('是否允许后台列表显示', 'adminlist', $field['adminlist'] ?? '1', [
                '0' => '不允许',
                '1' => '允许',
            ]));
            $res[] = (new FieldSelect('数据源', 'dict_id', $field['dict_id'] ?? '', (function () use ($db): array {
                $res = [];
                foreach ($db->select('psrphp_cms_dict', '*') as $vo) {
                    $res[] = [
                        'title' => $vo['title'],
                        'value' => $vo['id'],
                    ];
                }
                return $res;
            })()))->set('required', true)->set('help', '<a href="' . $router->build('/psrphp/cms/dict/index') . '">管理数据源</a>');
            $res[] = (new Radio('是否允许后台筛选', 'adminfilter', $field['adminfilter'] ?? '1', [
                '0' => '不允许',
                '1' => '允许',
            ]));
            return $res;
        });
    }
    public static function onUpdateFieldData(): ?string
    {
        return null;
    }

    public static function onCreateContentForm(array $field, $value): array
    {
        return Framework::execute(function (
            Db $db,
        ) use ($field, $value) {
            $res = [];
            $res[] = new FieldSelect($field['title'], $field['name'], $value, (function () use ($db, $field): array {
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
        return Framework::execute(function (
            Db $db
        ) use ($field, $value): array {
            $res = [];
            $res[] = new FieldSelect($field['title'], $field['name'], $value, (function () use ($db, $field): array {
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
                    'sql' => '`' . $field['name'] . '` in (' . implode(',', $vls) . ')',
                    'binds' => [],
                ];
            } else {
                return [];
            }
        });
    }

    public static function onContentSearch(array $field, string $value): array
    {
        return [];
    }

    public static function onFilter(array $field): string
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

    public static function onShow(array $field, $value): string
    {
        return Framework::execute(function (
            Db $db,
            Template $template
        ) use ($field, $value) {
            $tpl = <<<'str'
<div style="display: flex;flex-direction: wrap;flex-wrap: wrap;gap: 5px;">
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
