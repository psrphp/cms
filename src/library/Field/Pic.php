<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Field;

use PsrPHP\Database\Db;
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

    public static function onCreateFieldForm(): array
    {
        $res = [];
        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', '1', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
        $res[] = (new Radio('是否允许后台列表显示', 'adminlist', '0', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }

    public static function onCreateFieldData()
    {
        Framework::execute(function (
            Db $db,
            Request $request
        ) {
            $model = $db->get('psrphp_cms_model', '*', [
                'id' => $request->post('model_id'),
            ]);
            $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD `' . $request->post('name') . '` varchar(255) NOT NULL DEFAULT \'\'');
        });
    }

    public static function onUpdateFieldForm(array $field): array
    {
        $res = [];
        $res[] = (new Radio('是否允许通过表单编辑', 'adminedit', $field['adminedit'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]))->set('help', '某些数据为程序更新的可设置为不可编辑，比如点击量，用户评分等等');
        $res[] = (new Radio('是否允许后台列表显示', 'adminlist', $field['adminlist'] ?? '1', [
            '0' => '不允许',
            '1' => '允许',
        ]));
        return $res;
    }

    public static function onUpdateFieldData(): ?string
    {
        return null;
    }

    public static function onCreateContentForm(array $field, $value): array
    {
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $res[] = new Cover($field['title'], $field['name'], $value, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }
    public static function onCreateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field): ?string {
            if ($request->has('post.' . $field['name'])) {
                return $request->post($field['name']);
            }
        });
    }

    public static function onUpdateContentForm(array $field, $value): array
    {
        return Framework::execute(function (
            Router $router
        ) use ($field, $value): array {
            $res = [];
            $res[] = new Cover($field['title'], $field['name'], $value, $router->build('/psrphp/admin/tool/upload'));
            return $res;
        });
    }

    public static function onUpdateContentData(array $field): ?string
    {
        return Framework::execute(function (
            Request $request,
        ) use ($field) {
            return $request->post($field['name']);
        });
    }

    public static function onContentFilter(array $field, $value): array
    {
        return [];
    }

    public static function onContentSearch(array $field, string $value): array
    {
        return [];
    }

    public static function onFilter(array $field): string
    {
        return '';
    }

    public static function onShow(array $field, $value): string
    {
        return '<img src="' . $value . '" alt="" width="100" height="100">';
    }
}
