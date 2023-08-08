<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Cms\Model\CategoryProvider;
use App\Psrphp\Cms\Model\ContentProvider;
use PsrPHP\Database\Db;
use PsrPHP\Pagination\Pagination;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Db $db,
        Request $request,
        Template $template,
        Pagination $pagination
    ) {
        $models = $db->select('psrphp_cms_model', '*');
        $model_id = $request->get('model_id');
        if ($model_id) {
            $model = $db->get('psrphp_cms_model', '*', [
                'id' => $model_id,
            ]);

            $categorys = [];
            foreach (CategoryProvider::getInstance($model['id'])->all() as $vo) {
                $categorys[$vo['name']] = [
                    'name' => $vo['name'],
                    'value' => $vo['name'],
                    'title' => $vo['title'],
                    'parent' => $vo['parent'],
                    'group' => $vo['group'],
                ];
            }

            $fields = $db->select('psrphp_cms_field', '*', [
                'model_id' => $model['id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]);

            if (strlen($request->get('category_name', ''))) {
                $category_names = $this->getSubCategorys($categorys, $request->get('category_name', ''));
            } else {
                $category_names = [];
            }

            $qs = [];
            $q = $request->get('q', '');
            if (is_string($q) && strlen($q)) {
                foreach ($fields as $vo) {
                    if ($vo['adminsearch']) {
                        $qs[$vo['name']] = '%' . $q . '%';
                    }
                }
            }

            $contentProvider = ContentProvider::getInstance(
                $model['id'],
                $category_names,
                $request->get('filter', []),
                $qs
            );

            $total = $contentProvider->getTotal();
            $page = intval($request->get('page', 1)) ?: 1;
            $size = min(100, intval($request->get('size', 20)) ?: 20);
            $contents = $contentProvider->select(
                $request->get('order', [
                    'id' => 'DESC',
                ]),
                $page,
                $size
            );

            return $template->renderFromFile('content/index@psrphp/cms', [
                'model' => $model,
                'models' => $models,
                'fields' => $fields,
                'categorys' => $categorys,
                'contents' => $contents,
                'total' => $total,
                'page' => $page,
                'size' => $size,
                'pagination' => $pagination->render($page, $total, $size),
            ]);
        } else {
            return $template->renderFromFile('content/index@psrphp/cms', [
                'models' => $models,
            ]);
        }
    }

    private function getSubCategorys(array $categorys, string $category_name): array
    {
        $res = [];
        foreach ($categorys as $vo) {
            if ($vo['parent'] == $category_name) {
                array_push($res, ...$this->getSubCategorys($categorys, $vo['name']));
            }
        }
        $res[] = $category_name;
        return $res;
    }
}
