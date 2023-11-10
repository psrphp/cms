<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

class Virtual implements FieldInterface
{
    public static function getTitle(): string
    {
        return '虚拟字段';
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
        return [];
    }

    public static function getCreateFieldSql(array $model, array $field): ?string
    {
        return null;
    }

    public static function getUpdateFieldForm(array $field): array
    {
        return [];
    }

    public static function getCreateContentForm(array $field, array $content): array
    {
        return [];
    }
    public static function getCreateContentData(array $field, array &$content)
    {
    }
    public static function getUpdateContentForm(array $field, array $content): array
    {
        return [];
    }
    public static function getUpdateContentData(array $field, array &$content)
    {
    }

    public static function getFilterForm(array $field, $value = null): ?string
    {
        return null;
    }
    
    public static function buildFilterSql(array $field, $value): array
    {
        return [];
    }

    public static function parseToHtml(array $field, array $content): string
    {
        return '<span style="color:red;">虚拟字段请在模型管理->字段管理->该虚拟字段自定义列表显示模板</span>';
    }
}
