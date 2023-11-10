<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\SelectLevel;
use PsrPHP\Form\Textarea;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use Stringable;

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
        $res = [];
        $res[] = (new Textarea('选项', 'items'))->setRequired()->setHelp('一行一个，格式：标题|值|父级值');
        return $res;
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` varchar(80) NOT NULL DEFAULT \'\'';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Textarea('选项', 'items', $field['items']))->setRequired()->setHelp('一行一个，格式：标题|值|父级值');
        return $res;
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new SelectLevel($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? '', (function () use ($field): array {
            $items = [];
            foreach (array_filter(explode(PHP_EOL, $field['items'])) as $vo) {
                $tmp = explode('|', $vo . '||||');
                $items[] = [
                    'title' => $tmp[0],
                    'value' => $tmp[1],
                    'parent' => $tmp[2],
                    'disabled' => $tmp[3] ? true : false,
                    'group' => $tmp[4],
                ];
            }
            return $items;
        })()))->setHelp($field['tips'] ?? '');
        return $res;
    }

    public static function getCreateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name'], '');
        });
    }

    public static function getUpdateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new SelectLevel($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? '', (function () use ($field): array {
            $items = [];
            foreach (array_filter(explode(PHP_EOL, $field['items'])) as $vo) {
                $tmp = explode('|', $vo . '||||');
                $items[] = [
                    'title' => $tmp[0],
                    'value' => $tmp[1],
                    'parent' => $tmp[2],
                    'disabled' => $tmp[3] ? true : false,
                    'group' => $tmp[4],
                ];
            }
            return $items;
        })()))->setHelp($field['tips'] ?? '');
        return $res;
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name'], 0);
        });
    }

    public static function getFilterForm(array $field, $value = null): string|Stringable
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field) {
            $items = [];
            foreach (array_filter(explode(PHP_EOL, $field['items'])) as $vo) {
                $tmp = explode('|', $vo . '||||');
                $items[] = [
                    'title' => $tmp[0],
                    'value' => $tmp[1],
                    'parent' => $tmp[2],
                    'disabled' => $tmp[3] ? true : false,
                    'group' => $tmp[4],
                ];
            }
            return str_replace('<div style="margin-bottom: 5px;">' . $field['title'] . '</div>', '', (new SelectLevel($field['title'], 'filter[' . $field['name'] . ']', $request->get('filter.' . $field['name']), $items)) . '');
        });
    }

    public static function buildFilterSql(array $field, $value): array
    {
        $getsubval = function ($items, $val) use (&$getsubval): array {
            $res = [];
            array_push($res, addslashes($val));
            foreach ($items as $vo) {
                if ($vo['parent'] === $val) {
                    array_push($res, ...$getsubval($items, $vo['value']));
                }
            }
            return $res;
        };

        $items = [];
        foreach (array_filter(explode(PHP_EOL, $field['items'])) as $vo) {
            $tmp = explode('|', $vo . '||||');
            $items[] = [
                'title' => $tmp[0],
                'value' => $tmp[1],
                'parent' => $tmp[2],
                'disabled' => $tmp[3] ? true : false,
                'group' => $tmp[4],
            ];
        }

        $vls = [];
        foreach ((array)$value as $tmp) {
            $tmp = trim($tmp);
            if (!is_null($tmp) && strlen($tmp)) {
                array_push($vls, ...$getsubval($items, $tmp));
            }
        }
        if ($vls) {
            return [
                'where' => '`' . $field['name'] . '` in (\'' . implode('\',\'', $vls) . '\')',
                'binds' => [],
            ];
        } else {
            return [];
        }
    }

    public static function parseToHtml(array $field, array $content): string
    {

        if (!isset($content[$field['name']])) {
            return '';
        }
        $value = $content[$field['name']];

        foreach (array_filter(explode(PHP_EOL, $field['items'])) as $vo) {
            $tmp = explode('|', $vo . '|');
            if ($tmp[1] == $value) {
                return $tmp[0];
            }
        }

        return $value;
    }
}
