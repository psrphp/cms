{include common/header@psrphp/admin}
<h1>数据管理</h1>
<div>
    <a href="{:$router->build('/psrphp/cms/data/create', ['dict_id'=>$dict['id'], 'parent'=>$request->get('parent')])}">新增</a>
</div>
<table>
    <thead>
        <tr>
            <th>标题</th>
            <th>别名</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
        {foreach $datas as $vo}
        <tr>
            <td>
                {$vo.title}
            </td>
            <td>
                {$vo.alias}
            </td>
            <td>
                <a href="{:$router->build('/psrphp/cms/data/update', ['id'=>$vo['id']])}">编辑</a>
                <a href="{:$router->build('/psrphp/cms/data/delete', ['id'=>$vo['id']])}" onclick="return confirm('确定删除吗？删除后不可恢复！');">删除</a>
                <a href="{:$router->build('/psrphp/cms/data/index', ['dict_id'=>$dict['id'], 'parent'=>$vo['value']])}">下一级</a>
                <a href="{echo $router->build('/psrphp/cms/data/priority', ['id'=>$vo['id'],'type'=>'up'])}">上移</a>
                <a href="{echo $router->build('/psrphp/cms/data/priority', ['id'=>$vo['id'],'type'=>'down'])}">下移</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{include common/footer@psrphp/admin}