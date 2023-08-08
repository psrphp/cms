{include common/header@psrphp/admin}
<div class="container">
    <div class="my-4">
        <div class="h1">字段管理</div>
        <div class="text-muted fw-light">
            <span>管理模型字段~</span>
        </div>
    </div>

    <div class="table-responsive my-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-nowrap">标题</th>
                    <th class="text-nowrap">字段</th>
                    <th class="text-nowrap">类型</th>
                    <th class="text-nowrap">后台编辑</th>
                    <th class="text-nowrap">后台列表显示</th>
                    <th class="text-nowrap">后台筛选</th>
                    <th class="text-nowrap">后台排序</th>
                    <th class="text-nowrap">后台检索</th>
                    <th class="text-nowrap">管理</th>
                </tr>
            </thead>
            <tbody>
                {foreach $fields as $vo}
                <tr>
                    <td>
                        {$vo.title}
                    </td>
                    <td>
                        {$vo.name}
                        {if $vo['system']}
                        <span class="badge text-bg-secondary">系统</span>
                        {/if}
                    </td>
                    <td>
                        {$vo.type}
                    </td>
                    <td>
                        {if $vo['adminedit']}
                        <span>允许</span>
                        {else}
                        <span>-</span>
                        {/if}
                    </td>
                    <td>
                        {if $vo['adminlist']}
                        <span>允许</span>
                        {else}
                        <span>-</span>
                        {/if}
                    </td>
                    <td>
                        {if $vo['adminfilter']}
                        <span>允许</span>
                        {else}
                        <span>-</span>
                        {/if}
                    </td>
                    <td>
                        {if $vo['adminorder']}
                        <span>允许</span>
                        {else}
                        <span>-</span>
                        {/if}
                    </td>
                    <td>
                        {if $vo['adminsearch']}
                        <span>允许</span>
                        {else}
                        <span>-</span>
                        {/if}
                    </td>
                    <td>
                        {if !$vo['system']}
                        <a href="{:$router->build('/psrphp/cms/field/update', ['id'=>$vo['id']])}">编辑</a>
                        <a href="{:$router->build('/psrphp/cms/field/delete', ['id'=>$vo['id']])}" onclick="return confirm('确定删除吗？删除后不可恢复！');">删除</a>
                        {/if}
                        <a href="{echo $router->build('/psrphp/cms/field/priority', ['id'=>$vo['id'],'type'=>'up'])}">上移</a>
                        <a href="{echo $router->build('/psrphp/cms/field/priority', ['id'=>$vo['id'],'type'=>'down'])}">下移</a>
                    </td>
                </tr>
                {/foreach}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9">
                        添加：
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'select'])}">单选</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'checkbox'])}">多选</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'text'])}">单行文本</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'textarea'])}">多行文本</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'markdown'])}">Markdown</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'code'])}">代码编辑器</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'editor'])}">富文本编辑器</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'int'])}">整数</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'float'])}">浮点数(小数)</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'datetime'])}">日期时间</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'date'])}">日期</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'time'])}">时间</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'pic'])}">单图</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'pics'])}">多图</a>
                        <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>'files'])}">附件</a>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
{include common/footer@psrphp/admin}