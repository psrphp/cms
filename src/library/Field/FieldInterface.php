<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use Stringable;

interface FieldInterface
{
    public static function getTitle(): string;
    public static function isOrderable(): bool;
    public static function isSearchable(): bool;

    public static function getCreateFieldForm(): ?array;
    public static function getCreateFieldSql(array $model, array $field): ?string;
    public static function getUpdateFieldForm(array $field): ?array;

    public static function getCreateContentForm(array $field, array $data): ?array;
    public static function getCreateContentData(array $field, array &$data);
    public static function getUpdateContentForm(array $field, array $data): ?array;
    public static function getUpdateContentData(array $field, array &$data);

    public static function getFilterForm(array $field): null|string|int|float|Stringable;
    public static function buildFilterSql(array $field, $value): ?array;

    public static function parseToHtml(array $field, array $content): null|string|int|float|Stringable;
}
