{include common/header@psrphp/admin}
<div class="container">
    <div class="my-4">
        <div class="h1">模型管理</div>
        <div class="text-muted fw-light">
            <span>管理系统模型~</span>
        </div>
    </div>
    <div class="my-4">
        <a class="btn btn-primary" href="{:$router->build('/psrphp/cms/model/create')}">新增</a>
    </div>
    <div class="table-responsive my-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-nowrap">模型</th>
                    <th class="text-nowrap">表</th>
                    <th class="text-nowrap">管理</th>
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
    </div>
</div>
{include common/footer@psrphp/admin}