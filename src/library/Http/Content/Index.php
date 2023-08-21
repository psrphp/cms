<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use App\Psrphp\Cms\Model\CategoryProvider;
use App\Psrphp\Cms\Model\ContentProvider;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Db $db,
        Request $request,
        Template $template,
        ContentProvider $contentProvider
    ) {
        $models = $db->select('psrphp_cms_model', '*');
        $model_id = $request->get('model_id');
        if ($model_id) {
            if (!$model = $db->get('psrphp_cms_model', '*', [
                'id' => $model_id,
            ])) {
                return Response::error('模型不存在');
            }

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
            foreach ($fields as &$vo) {
                $vo = array_merge(json_decode($vo['extra'], true), $vo);
            }

            if (strlen($request->get('category_name', ''))) {
                $category_names = $this->getSubCategorys($categorys, $request->get('category_name', ''));
            } else {
                $category_names = [];
            }

            $filters = $request->get('filter', []);
            $q = $request->get('q', '');
            if (is_string($q) && strlen($q)) {
                foreach ($fields as $vo) {
                    if ($vo['adminsearch']) {
                        $filters[$vo['name']] = '%' . $q . '%';
                    }
                }
            }

            $page = intval($request->get('page', 1)) ?: 1;
            $size = min(100, intval($request->get('size', 20)) ?: 20);
            $res = $contentProvider->select(
                $model['id'],
                $category_names,
                $filters,
                $request->get('order', [
                    'id' => 'DESC',
                ]),
                $page,
                $size
            );

            $data['maxpage'] = ceil($res['total'] / $size) ?: 1;

            return $template->renderFromFile('content/index@psrphp/cms', [
                'model' => $model,
                'models' => $models,
                'fields' => $fields,
                'categorys' => $categorys,
                'contents' => $res['contents'],
                'total' => $res['total'],
                'maxpage' => ceil($res['total'] / $size) ?: 1,
                'page' => $page,
                'size' => $size,
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
