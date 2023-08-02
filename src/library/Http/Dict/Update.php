<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Http\Dict;

use App\Psrphp\Admin\Http\Common;
use App\Psrphp\Admin\Lib\Response;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
use PsrPHP\Request\Request;

class Update extends Common
{
    public function get(
        Db $db,
        Request $request,
    ) {
        $dict = $db->get('psrphp_cms_dict', '*', [
            'id' => $request->get('id'),
        ]);
        $form = new Builder('编辑数据源');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-8'))->addItem(
                    (new Hidden('id', $dict['id'])),
                    (new Input('标题', 'title', $dict['title']))->set('help', '例如：'),
                )
            )
        );
        return $form;
    }

    public function post(
        Db $db,
        Request $request,
    ) {
        $dict = $db->get('psrphp_cms_dict', '*', [
            'id' => $request->post('id'),
        ]);

        $update = array_intersect_key($request->post(), [
            'title' => '',
        ]);

        $db->update('psrphp_cms_dict', $update, [
            'id' => $dict['id'],
        ]);

        return Response::success('操作成功！');
    }
}
