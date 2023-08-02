<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Content;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Cms\Model\CategoryProvider;
use App\Psrphp\Cms\Model\ContentProvider;
use App\Psrphp\Cms\Model\FieldProvider;
use App\Psrphp\Cms\Model\Model;
use App\Psrphp\Cms\Model\ModelProvider;
use PsrPHP\Pagination\Pagination;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Request $request,
        Template $template,
        Pagination $pagination
    ) {

        $modelProvider = ModelProvider::getInstance();
        $model_id = $request->get('model_id');
        if ($model_id) {
            $model = Model::getInstance(intval($model_id));
            $fieldProvider = FieldProvider::getInstance($model['id']);

            $page = $request->get('page', 1) ?: 1;
            $size = min(100, $request->get('size', 20) ?: 20);

            $contentProvider = ContentProvider::getInstance(
                $model['id'],
                $request->get('category_id'),
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
                'modelProvider' => $modelProvider,
                'fieldProvider' => $fieldProvider,
                'categoryProvider' => CategoryProvider::getInstance($model['id']),
                'contentProvider' => $contentProvider,
                'total' => $total,
                'page' => $page,
                'size' => $size,
                'pagination' => $pagination->render($page, $total, $size),
            ]);
        } else {
            return $template->renderFromFile('content/index@psrphp/cms', [
                'modelProvider' => $modelProvider,
            ]);
        }
    }
}
