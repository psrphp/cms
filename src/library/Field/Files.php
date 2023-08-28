<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Field\Files as FieldFiles;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;
use PsrPHP\Template\Template;

class Files implements FieldInterface
{
    public static function getTitle(): string
    {
        return '附件';
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

    public static function getCreateFieldSql(string $model_name, string $field_name): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` text';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        return $res;
    }

    public static function getCreateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $val = is_null($value) ? [] : json_decode($value, true);
            $res[] = new FieldFiles($field['title'], $field['name'], $val, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function getCreateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): ?string {
            return json_encode(
                $request->post($field['name'], []),
                JSON_UNESCAPED_UNICODE
            );
        });
    }

    public static function getUpdateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $val = is_null($value) ? [] : json_decode($value, true);
            $res[] = new FieldFiles($field['title'], $field['name'], $val, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function getUpdateContentData(array $field, $oldvalue): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): ?string {
            return json_encode(
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

    public static function parseToHtml(array $field, $value, array $content): string
    {
        return Framework::execute(function (
            Template $template
        ) use ($field, $value) {
            $tpl = <<<'str'
<div>
    {foreach $items as $vo}
    <div>
        <a href="{$vo.src}">{$vo.title}({$vo.size})</a>
    </div>
    {/foreach}
</div>
str;
            return $template->renderFromString($tpl, [
                'field' => $field,
                'items' => is_null($value) ? [] : json_decode($value, true),
            ]);
        });
    }
}
