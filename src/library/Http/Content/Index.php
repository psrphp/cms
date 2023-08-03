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
            $fields = $db->select('psrphp_cms_field', '*', [
                'model_id' => $model['id'],
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]);
            $page = $request->get('page', 1) ?: 1;
            $size = min(100, $request->get('size', 20) ?: 20);

            $contentProvider = ContentProvider::getInstance(
                $model['id'],
                $request->get('category_name'),
                $request->get('filter', []),
                $request->get('order', [
                    'id' => 'DESC',
                ]),
                $request->get('q', ''),
                (int)$page,
                (int)$size
            );

            $total = $contentProvider->getTotal();

            return $template->renderFromFile('content/index@psrphp/cms', [
                'model' => $model,
                'models' => $models,
                'fields' => $fields,
                'categoryProvider' => CategoryProvider::getInstance($model['id']),
                'contentProvider' => $contentProvider,
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
}
