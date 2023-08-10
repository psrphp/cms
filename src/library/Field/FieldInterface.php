<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use Stringable;

interface FieldInterface
{
    public static function getTitle(): string;
    public static function onCreateFieldForm(): array;
    public static function onCreateFieldData();
    public static function onUpdateFieldForm(array $field): array;
    public static function onUpdateFieldData();
    public static function onCreateContentForm(array $field, null|int|float|string $value): array;
    public static function onCreateContentData(array $field);
    public static function onUpdateContentForm(array $field, null|int|float|string $value): array;
    public static function onUpdateContentData(array $field);
    public static function onContentFilter(array $field, $value): ?array;
    public static function onContentSearch(array $field, string $value): ?array;
    public static function onFilter(array $field);
    public static function onShow(array $field, $value);
}
