<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Form\Field\Cover;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Framework\Framework;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;

class Pic implements FieldInterface
{
    public static function getTitle(): string
    {
        return '单图';
    }

    public static function getCreateFieldForm(): array
    {
        $res = [];
        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
        return $res;
    }

    public static function getCreateFieldSql(string $model_name, string $field_name): string
    {
        return 'ALTER TABLE <psrphp_cms_content_' . $model_name . '> ADD `' . $field_name . '` varchar(255) NOT NULL DEFAULT \'\'';
    }

    public static function getUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', $field['adminedit'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
        return $res;
    }

    public static function getCreateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $res[] = new Cover($field['title'], $field['name'], $value, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function getCreateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): ?string {
            if ($request->has('post.' . $field['name'])) {
                return $request->post($field['name']);
            }
        });
    }

    public static function getUpdateContentForm(array $field, $value = null): array
    {
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $res[] = new Cover($field['title'], $field['name'], $value, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function getUpdateContentData(array $field, $oldvalue): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field) {
            return $request->post($field['name']);
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

    public static function parseToHtml(array $field, $value): string
    {
        return '<img src="' . $value . '" alt="" width="100" height="100">';
    }
}
