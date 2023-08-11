<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Field;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Cms\Model\FieldProvider;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Config;
use PsrPHP\Request\Request;
use PsrPHP\Template\Template;

class Index extends Common
{
    public function get(
        Db $db,
        Request $request,
        Template $template
    ) {
        $model = $db->get('psrphp_cms_model', '*', [
            'id' => $request->get('model_id'),
        ]);
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
        return $template->renderFromFile('field/index@psrphp/cms', [
            'model' => $model,
            'fields' => $fields,
            'fieldProvider' => FieldProvider::getInstance(),
        ]);
    }

    public function test(
        Config $config
    ) {
        $config->get('app.install@psrphp/cms')();
    }
}
