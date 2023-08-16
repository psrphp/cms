{include common/header@psrphp/admin}
<h1>模型管理</h1>
<div>
    <a href="{:$router->build('/psrphp/cms/model/create')}">新增</a>
</div>
<table>
    <thead>
        <tr>
            <th>模型</th>
            <th>表</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
        {foreach $models as $vo}
        <tr>
            <td>
                {$vo.title}
            </td>
            <td>
                {$vo.name}
            </td>
            <td>
                <a href="{:$router->build('/psrphp/cms/model/update', ['id'=>$vo['id']])}">编辑</a>
                <a href="{:$router->build('/psrphp/cms/model/delete', ['id'=>$vo['id']])}" onclick="return confirm('确定删除吗？删除后不可恢复！');">删除</a>
                <a href="{:$router->build('/psrphp/cms/field/index', ['model_id'=>$vo['id']])}">字段管理</a>
                <a href="{:$router->build('/psrphp/cms/content/index', ['model_id'=>$vo['id']])}">内容管理</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{include common/footer@psrphp/admin}