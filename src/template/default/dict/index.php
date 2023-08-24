{include common/header@psrphp/admin}
<h1>数据源管理</h1>
<div>
    <a href="{:$router->build('/psrphp/cms/dict/create')}">新增</a>
</div>
<table style="margin-top: 15px;">
    <thead>
        <tr>
            <th>数据源</th>
            <th>管理</th>
        </tr>
    </thead>
    <tbody>
        {foreach $dicts as $vo}
        <tr>
            <td>
                {$vo.title}
            </td>
            <td>
                <a href="{:$router->build('/psrphp/cms/dict/update', ['id'=>$vo['id']])}">编辑</a>
                <a href="{:$router->build('/psrphp/cms/dict/delete', ['id'=>$vo['id']])}" onclick="return confirm('确定删除吗？删除后不可恢复！');">删除</a>
                <a href="{:$router->build('/psrphp/cms/data/index', ['dict_id'=>$vo['id']])}">数据管理</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{include common/footer@psrphp/admin}