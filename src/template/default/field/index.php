{include common/header@psrphp/admin}
<h1>字段管理</h1>

<fieldset>
    <legend>添加字段：</legend>
    {foreach $fieldProvider->all() as $vo}
    <a href="{:$router->build('/psrphp/cms/field/create', ['model_id'=>$model['id'], 'type'=>$vo])}">{:$vo::getTitle()}</a>
    {/foreach}
</fieldset>

<table style="margin-top: 15px;">
    <thead>
        <tr>
            <th>标题</th>
            <th>字段</th>
            <th>类型</th>
            <th>后台列表显示</th>
            <th>后台编辑</th>
            <th>后台筛选</th>
            <th>后台排序</th>
            <th>管理</th>
            <th>排序</th>
        </tr>
    </thead>
    <tbody>
        <?php $groups = []; ?>
        {foreach $fields as $vo}
        <?php $vo['group'] = $vo['group'] ?: '未分组'; ?>
        {if !in_array($vo['group'], $groups)}
        <?php $groups[] = $vo['group']; ?>
        <tr>
            <td colspan="9">
                <span style="font-weight: bold;">{$vo['group']}</span>
            </td>
        </tr>
        {foreach $fields as $sub}
        <?php $sub['group'] = $sub['group'] ?: '未分组'; ?>
        {if $sub['group'] == $vo['group']}
        <tr>
            <td>
                <span>{$sub.title}</span>
            </td>
            <td>
                {$sub.name}
            </td>
            <td>
                {$sub.type}
            </td>
            <td>
                {if $sub['adminlist']}
                <span>允许</span>
                {else}
                <span>-</span>
                {/if}
            </td>
            <td>
                {if $sub['adminedit']}
                <span>允许</span>
                {else}
                <span>-</span>
                {/if}
            </td>
            <td>
                {if $sub['adminfilter']}
                <span>允许</span>
                {else}
                <span>-</span>
                {/if}
            </td>
            <td>
                {if $sub['adminorder']}
                <span>允许</span>
                {else}
                <span>-</span>
                {/if}
            </td>
            <td>
                {if $sub['type']}
                <a href="{:$router->build('/psrphp/cms/field/update', ['id'=>$sub['id']])}">编辑</a>
                {/if}
                {if !$sub['system']}
                <a href="{:$router->build('/psrphp/cms/field/delete', ['id'=>$sub['id']])}" onclick="return confirm('确定删除吗？删除后不可恢复！');">删除</a>
                {/if}
            </td>
            <td>
                <a href="{echo $router->build('/psrphp/cms/field/priority', ['id'=>$sub['id'],'type'=>'up'])}">上移</a>
                <a href="{echo $router->build('/psrphp/cms/field/priority', ['id'=>$sub['id'],'type'=>'down'])}">下移</a>
            </td>
        </tr>
        {/if}
        {/foreach}
        {/if}
        {/foreach}
    </tbody>
</table>
{include common/footer@psrphp/admin}