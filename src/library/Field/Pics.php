<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Pics as FieldPics;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;
use PsrPHP\Template\Template;

class Pics implements FieldInterface
{
    public static function getTitle(): string
    {
        return '多图';
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
        return $res;
    }

    public static function getCreateFieldSql(array $model, array $field): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` text';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        return $res;
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $value = json_decode($content[$field['name']] ?? '[]', true);
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $res[] = new FieldPics($field['title'], $field['name'], $value, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function getCreateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = json_encode(
                $request->post($field['name'], []),
                JSON_UNESCAPED_UNICODE
            );
        });
    }

    public static function getUpdateContentForm(array $field, array $content): array
    {
        $value = json_decode($content[$field['name']] ?? '[]', true);
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $res[] = new FieldPics($field['title'], $field['name'], $value, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = json_encode(
                $request->post($field['name'], []),
                JSON_UNESCAPED_UNICODE
            );
        });
    }

    public static function buildFilterSql(array $field, $value): array
    {
        return [];
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return '';
    }

    public static function parseToHtml(array $field, array $content): string
    {
        return Framework::execute(function (
            Template $template
        ) use ($field, $content): string {
            $tpl = <<<'str'
<div style="display: flex;flex-direction: wrap;flex-wrap: wrap;gap: 5px;">
    {foreach $items as $vo}
    <div>
        <img src="{$vo.src}" alt="" width="100" height="100">
    </div>
    {/foreach}
</div>
str;
            return $template->renderFromString($tpl, [
                'field' => $field,
                'items' => json_decode($content[$field['name']], true),
            ]);
        });
    }
}
