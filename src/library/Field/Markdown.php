<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\SimpleMDE;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;

class Markdown implements FieldInterface
{
    public static function getTitle(): string
    {
        return 'Markdown编辑器';
    }

    public static function isOrderable(): bool
    {
        return false;
    }

    public static function isSearchable(): bool
    {
        return true;
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
        return Framework::execute(function (
            Router $router
        ) use ($field, $content): array {
            $res = [];
            $res[] = (new SimpleMDE($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? '', $router->build('/psrphp/admin/tool/upload')))->setHelp($field['tips'] ?? '');
            return $res;
        });
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
        return Framework::execute(function (
            Router $router
        ) use ($field, $content): array {
            $res = [];
            $res[] = (new SimpleMDE($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? '', $router->build('/psrphp/admin/tool/upload')))->setHelp($field['tips'] ?? '');
            return $res;
        });
    }

    public static function getUpdateContentData(array $field, array &$content)
    {
        Framework::execute(function (
            Request $request,
        ) use ($field, &$content) {
            $content[$field['name']] = $request->post($field['name']);
        });
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return '';
    }

    public static function buildFilterSql(array $field, $value): array
    {
        return [];
    }

    public static function parseToHtml(array $field, array $content): string
    {
        // todo..
        return '<pre>' . $content[$field['name']] . '</pre>';
    }
}
