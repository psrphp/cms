{include common/header@psrphp/admin}
<h1>内容管理</h1>

<div style="display: flex;gap: 10px;margin-bottom: 10px;">
    <form action="{echo $router->build('/psrphp/cms/content/index')}" method="GET">
        <fieldset>
            <legend>模型</legend>
            <select name="model_id" onchange="this.form.submit();">
                <option value="">请选择</option>
                {foreach $models as $vo}
                {if $request->get('model_id')==$vo['id']}
                <option selected value="{$vo.id}">{$vo.title}</option>
                {else}
                <option value="{$vo.id}">{$vo.title}</option>
                {/if}
                {/foreach}
            </select>
        </fieldset>
    </form>
</div>

{if isset($model)}
<form action="{echo $router->build('/psrphp/cms/content/index')}" onchange="this.submit()">
    <input type="hidden" name="model_id" value="{$model.id}">
    <div style="display: flex;flex-direction: row;flex-wrap: wrap;gap: 10px;">
        {foreach $fields as $field}
        {if $field['type']}
        {if $tmp = $field['type']::getFilterForm($field)}
        <fieldset>
            <legend>{$field['title']}:</legend>
            <div>{echo $tmp}</div>
        </fieldset>
        {/if}
        {/if}
        {/foreach}
        <fieldset>
            <legend>搜索:</legend>
            <input type="search" name="q" value="{$request->get('q')}" placeholder="请输入搜索词：">
        </fieldset>
    </div>
    <div style="display: flex;flex-direction: row;flex-wrap: wrap;gap: 10px;margin-top: 10px;">
        <fieldset>
            <legend>排序:</legend>
            <div style="display: flex;flex-direction: row;flex-wrap: wrap;gap: 5px;">
                {foreach $fields as $field}
                {if ($field['name']=='id') || ($field['type'] && $field['type']::isOrderable())}
                <div>
                    <input type="radio" style="display: none;" name="order[{$field.name}]" value="{$request->get('order.'.$field['name'])}" checked>
                    {if $request->get('order.'.$field['name']) == 'desc'}
                    <label>
                        <input type="radio" style="display: none;" name="order[{$field.name}]" value="asc">
                        <span style="color:red;">{$field.title}↓</span>
                    </label>
                    {elseif $request->get('order.'.$field['name']) == 'asc'}
                    <label>
                        <input type="radio" style="display: none;" name="order[{$field.name}]" value="">
                        <span style="color:red;">{$field.title}↑</span>
                    </label>
                    {else}
                    <label>
                        <input type="radio" style="display: none;" name="order[{$field.name}]" value="desc">
                        <span>{$field.title}</span>
                    </label>
                    {/if}
                </div>
                {/if}
                {/foreach}
            </div>
        </fieldset>
    </div>
</form>

<div style="margin-top: 15px;">
    <a href="{echo $router->build('/psrphp/cms/content/create', ['model_id'=>$model['id']])}">添加内容</a>
</div>

<style>
    #tablemain tr.nowrap th,
    #tablemain tr.nowrap td {
        white-space: nowrap;
    }
</style>

<div style="overflow-x: auto;margin-top: 15px;">
    <table id="tablemain">
        <thead>
            <tr class="nowrap">
                <th style="width:22px;">#</th>
                <th>ID</th>
                <?php $fieldtypenum = 0; ?>
                {foreach $fields as $field}
                {if $field['type'] && $field['show']}
                <?php $fieldtypenum += 1; ?>
                <th>{$field.title}</th>
                {/if}
                {/foreach}
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            {foreach $contents as $content}
            <tr class="nowrap">
                <td>
                    <input type="checkbox" value="{$content.id}">
                </td>
                <td><span>{$content.id}</span></td>
                {foreach $fields as $field}
                {if $field['type'] && $field['show']}
                <td>
                    {if $field['tpl']}
                    {echo $template->renderFromString($field['tpl'], ['field'=>$field, 'content'=>$content])}
                    {else}
                    {echo $field['type']::parseToHtml($field, $content)}
                    {/if}
                </td>
                {/if}
                {/foreach}
                <td>
                    <a href="{echo $router->build('/psrphp/cms/content/update', ['model_id'=>$model['id'], 'id'=>$content['id']])}">编辑</a>
                    <a href="{echo $router->build('/psrphp/cms/content/create', ['model_id'=>$model['id'], 'copyfrom'=>$content['id']])}">复制</a>
                    <a href="javascript:void(0)" onclick="event.target.parentNode.parentNode.nextElementSibling.style.display=event.target.parentNode.parentNode.nextElementSibling.style.display=='table-row'?'none':'table-row'">详情</a>
                </td>
            </tr>
            <tr style="display: none;">
                <td colspan="{$fieldtypenum + 3}">
                    <dl>
                        <dt>ID</dt>
                        <dd>{$content.id}</dd>
                        {foreach $fields as $field}
                        {if $field['type']}
                        <dt>{$field.title}</dt>
                        <dd>
                            {if $field['tpl']}
                            {echo $template->renderFromString($field['tpl'], ['field'=>$field, 'content'=>$content])}
                            {else}
                            {echo $field['type']::parseToHtml($field, $content)}
                            {/if}
                        </dd>
                        {/if}
                        {/foreach}
                    </dl>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
<div style="display: flex;flex-wrap: wrap;gap: 5px;margin-top: 5px;">
    <div>
        <button type="button" id="fanxuan">全选/反选</button>
        <script>
            (function() {
                var fanxuanbtn = document.getElementById("fanxuan");
                fanxuanbtn.addEventListener('click', () => {
                    var checklist = document.querySelectorAll("#tablemain td input");
                    checklist.forEach(element => {
                        element.click();
                    });
                })
            })()
        </script>
    </div>

    <div>
        <form action="{echo $router->build('/psrphp/cms/content/delete')}" method="POST">
            <input type="hidden" name="model_id" value="{$model.id}">
            <input type="hidden" name="ids" value="">
            <button type="submit" onclick="return confirm('确定删除吗？删除后不可恢复！')">删除</button>
        </form>
    </div>
</div>
<script>
    (() => {
        var checklist = document.querySelectorAll("#tablemain td input");
        checklist.forEach(element => {
            element.addEventListener('click', () => {
                var checklist = document.querySelectorAll("#tablemain td input");
                var ids = [];
                checklist.forEach(ele => {
                    if (ele.checked) {
                        ids.push(ele.value);
                    }
                })
                document.getElementsByName("ids").forEach(ele => {
                    ele.value = ids.join(',');
                });
            })
        });
    })()
</script>

<div style="display: flex;flex-direction: row;flex-wrap: wrap;margin-top: 20px;gap: 5px;">
    <a href="{echo $router->build('/psrphp/cms/content/index', array_merge($_GET, ['page'=>1]))}">首页</a>
    <a href="{echo $router->build('/psrphp/cms/content/index', array_merge($_GET, ['page'=>max($request->get('page', 1)-1, 1)]))}">上一页</a>
    <a href="{echo $router->build('/psrphp/cms/content/index', array_merge($_GET, ['page'=>min($request->get('page', 1)+1, $maxpage)]))}">下一页</a>
    <a href="{echo $router->build('/psrphp/cms/content/index', array_merge($_GET, ['page'=>$maxpage]))}">末页</a>
</div>
{/if}
{include common/footer@psrphp/admin}