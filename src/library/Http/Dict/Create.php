<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Dict;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Input;
use PsrPHP\Request\Request;

class Create extends Common
{
    public function get()
    {
        $form = new Builder('添加数据源');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-9'))->addItem(
                    (new Input('标题', 'title'))->set('help', '例如：'),
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request
    ) {
        $db->insert('psrphp_cms_dict', [
            'title' => $request->post('title'),
        ]);

        return Response::success('操作成功！', 'javascript:history.go(-2)');
    }
}
