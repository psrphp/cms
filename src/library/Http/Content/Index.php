<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
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

            $filters = $request->get('filter', []);
            $total = $contentProvider->count(
                $model['id'],
                $filters,
            );

            $page = intval($request->get('page', 1)) ?: 1;
            $size = min(100, intval($request->get('size', 20)) ?: 20);
            $order = $request->get('order', [
                'id' => 'DESC',
            ]);
            $contents = $contentProvider->select(
                $model['id'],
                $filters,
                $request->get('q', ''),
                $order,
                $page,
                $size
            );

            return $template->renderFromFile('content/index@psrphp/cms', [
                'model' => $model,
                'models' => $models,
                'fields' => $fields,
                'contents' => $contents,
                'total' => $total,
                'maxpage' => ceil($total / $size) ?: 1,
                'page' => $page,
                'size' => $size,
            ]);
        } else {
            return $template->renderFromFile('content/index@psrphp/cms', [
                'models' => $models,
            ]);
        }
    }
}
