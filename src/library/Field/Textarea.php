<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Textarea as FieldTextarea;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;

class Textarea implements FieldInterface
{
    public static function getTitle(): string
    {
        return '短文本-多行';
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
        return 'ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $field['name'] . '` varchar(255) NOT NULL DEFAULT \'\'';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        return $res;
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        $res = [];
        $res[] = (new FieldTextarea($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? ''))->setHelp($field['tips'] ?? '');
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
        $res[] = (new FieldTextarea($field['title'], $field['name'], $content[$field['name']] ?? $field['default'] ?? ''))->setHelp($field['tips'] ?? '');
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
        return [];
    }

    public static function getFilterForm(array $field, $value = null): string
    {
        return '';
    }

    public static function parseToHtml(array $field, array $content): ?string
    {
        return $content[$field['name']];
    }
}
