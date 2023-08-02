{include common/header@psrphp/admin}
<div class="container">
    <div class="my-4">
        <div class="h1">数据源值管理</div>
        <div class="text-muted fw-light">
            <span>管理模型数据源值~</span>
        </div>
    </div>
    <div class="my-4">
        <a class="btn btn-primary" href="{:$router->build('/psrphp/cms/data/create', ['dict_id'=>$dict['id'], 'pid'=>$request->get('pid', 0)])}">新增</a>
    </div>
    <div class="table-responsive my-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-nowrap">标题</th>
                    <th class="text-nowrap">值</th>
                    <th class="text-nowrap">管理</th>
                </tr>
            </thead>
            <tbody>
                {foreach $datas as $vo}
                <tr>
                    <td>
                        {$vo.title}
                    </td>
                    <td>
                        {$vo.value}
                    </td>
                    <td>
                        <a href="{:$router->build('/psrphp/cms/data/update', ['id'=>$vo['id']])}">编辑</a>
                        <a href="{:$router->build('/psrphp/cms/data/delete', ['id'=>$vo['id']])}" onclick="return confirm('确定删除吗？删除后不可恢复！');">删除</a>
                        <a href="{:$router->build('/psrphp/cms/data/index', ['dict_id'=>$dict['id'], 'pid'=>$vo['id']])}">下一级</a>
                        <a href="{echo $router->build('/psrphp/cms/data/priority', ['id'=>$vo['id'],'type'=>'up'])}">上移</a>
                        <a href="{echo $router->build('/psrphp/cms/data/priority', ['id'=>$vo['id'],'type'=>'down'])}">下移</a>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
{include common/footer@psrphp/admin}