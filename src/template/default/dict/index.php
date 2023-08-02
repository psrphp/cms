{include common/header@psrphp/admin}
<div class="container">
    <div class="my-4">
        <div class="h1">数据源管理</div>
        <div class="text-muted fw-light">
            <span>管理系统数据源~</span>
        </div>
    </div>
    <div class="my-4">
        <a class="btn btn-primary" href="{:$router->build('/psrphp/cms/dict/create')}">新增</a>
    </div>
    <div class="table-responsive my-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-nowrap">数据源</th>
                    <th class="text-nowrap">管理</th>
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
    </div>
</div>
{include common/footer@psrphp/admin}