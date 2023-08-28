<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

interface FieldInterface
{
    public static function getTitle(): string;
    public static function isOrderable(): bool;
    public static function isSearchable(): bool;

    public static function getCreateFieldForm(): array;
    public static function getCreateFieldSql(string $model_name, string $field_name): string;
    public static function getUpdateFieldForm(array $field): array;

    public static function getCreateContentForm(array $field, $value = null): array;
    public static function getCreateContentData(array $field);
    public static function getUpdateContentForm(array $field, $value = null): array;
    public static function getUpdateContentData(array $field, $oldvalue);

    public static function getFilterForm(array $field): ?string;
    public static function buildFilterSql(array $field, $value): array;

    public static function parseToHtml(array $field, $value, array $content): string;
}
