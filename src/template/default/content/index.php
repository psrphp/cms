{include common/header@psrphp/admin}
<style>
    a {
        text-decoration: none;
    }
</style>
<div class="container">
    <div class="h1 my-4">内容管理</div>
    {if isset($model)}
    <div class="mb-3">
        <div class="d-flex gap-3">
            <form id="form_1" action="{echo $router->build('/psrphp/cms/content/index')}" method="GET">
                <div class="row gy-2 gx-3 align-items-center mb-3">
                    <div class="col-auto">
                        <label class="visually-hidden">模型</label>
                        <select class="form-select" name="model_id" onchange="document.getElementById('form_1').submit();">
                            {foreach $models as $vo}
                            <option {if $request->get('model_id')==$vo['id']}selected{/if} value="{$vo.id}">{$vo.title}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </form>
            <form id="form_2" action="{echo $router->build('/psrphp/cms/content/index')}" method="GET">
                <div class="row gy-2 gx-3 align-items-center mb-3">
                    <input type="hidden" name="model_id" value="{$model.id}">
                    <div class="col-auto selcc">
                        <style>
                            .selcc>div>.mt-2>label {
                                display: none;
                            }

                            .selcc>div>.mt-2 {
                                margin-top: 0 !important;
                            }
                        </style>
                        <?php
                        echo new PsrPHP\Form\Field\Select('分类', 'category_name', $request->get('category_name'), $categorys);
                        ?>
                    </div>
                </div>
            </form>
            <script>
                $("#form_2 select").bind('change', function() {
                    document.getElementById('form_2').submit();
                });
            </script>
        </div>
        <div>
            <form action="{echo $router->build('/psrphp/cms/content/index')}" id="form_3">
                <input type="hidden" name="model_id" value="{$model.id}">
                <input type="hidden" name="category_name" value="{$request->get('category_name')}">
                <div class="d-flex flex-column gap-2">
                    {foreach $fields as $field}
                    {if $field['type'] && $field['adminfilter']}
                    <div>
                        <div class="mb-1">
                            <span class="text-secondary small">{$field['title']}:</span>
                        </div>
                        <div>
                            {echo $field['type']::onFilter($field)}
                        </div>
                    </div>
                    {/if}
                    {/foreach}

                    <div>
                        <div class="mb-1">
                            <span class="text-secondary small">搜索:</span>
                        </div>
                        <div class="d-flex">
                            <div>
                                <input type="search" name="q" value="{$request->get('q')}" class="form-control form-control-sm" placeholder="请输入搜索词：">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1">
                            <span class="text-secondary small">排序:</span>
                        </div>
                        <div class="d-flex gap-2">
                            {foreach $fields as $field}
                            {if $field['adminorder']}
                            <div>
                                <input type="radio" class="d-none" name="order[{$field.name}]" value="{$request->get('order.'.$field['name'])}" autocomplete="off" checked>
                                {if $request->get('order.'.$field['name']) == 'desc'}
                                <input type="radio" class="d-none" name="order[{$field.name}]" value="asc" id="order_{$field.name}" autocomplete="off">
                                <label for="order_{$field.name}"><span class="badge text-bg-secondary">{$field.title}↓</span></label>
                                {elseif $request->get('order.'.$field['name']) == 'asc'}
                                <input type="radio" class="d-none" name="order[{$field.name}]" value="" id="order_{$field.name}" autocomplete="off">
                                <label for="order_{$field.name}"><span class="badge text-bg-secondary">{$field.title}↑</span></label>
                                {else}
                                <input type="radio" class="d-none" name="order[{$field.name}]" value="desc" id="order_{$field.name}" autocomplete="off">
                                <label for="order_{$field.name}"><span class="badge text-bg-light text-secondary">{$field.title}</span></label>
                                {/if}
                            </div>
                            {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </form>
            <script>
                $(function() {
                    $("#form_3 input").on("change", function() {
                        $("#form_3").submit();
                    });
                });
            </script>
        </div>
    </div>
    <div class="my-3">
        <a href="{echo $router->build('/psrphp/cms/content/create', ['model_id'=>$model['id']])}" class="btn btn-primary">添加内容</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered" id="tablexx">
            <thead>
                <tr>
                    <th class="text-nowrap" style="width:30px;">#</th>
                    <th class="text-nowrap">信息</th>
                </tr>
            </thead>
            <tbody>
                {foreach $contents as $content}
                <tr>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" name="ids[]" value="{$content.id}" class="form-check-input" id="checkbox_{$content.id}">
                        </div>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>ID：</td>
                                <td>
                                    {$content.id}
                                </td>
                            </tr>
                            <tr>
                                <td>栏目：</td>
                                <td>
                                    {if isset($categorys[$content['category_name']])}
                                    <a href="{echo $router->build('/psrphp/cms/content/index',['model_id'=>$model['id'], 'category_name'=>$content['category_name']])}">{$categorys[$content['category_name']]['title']}</a>
                                    {else}
                                    <a href="{echo $router->build('/psrphp/cms/content/index',['model_id'=>$model['id'], 'category_name'=>$content['category_name']])}">未知栏目</a>
                                    {/if}
                                </td>
                            </tr>
                            {foreach $fields as $field}
                            {if $field['type'] && $field['adminlist']}
                            <tr>
                                <td>{$field.title}：</td>
                                <td>
                                    {echo $field['type']::onShow($field, $content[$field['name']])}
                                </td>
                            </tr>
                            {/if}
                            {/foreach}
                        </table>
                        <div>
                            <a href="{echo $router->build('/psrphp/cms/content/update', ['model_id'=>$model['id'], 'id'=>$content['id']])}">编辑</a>
                            <a href="{echo $router->build('/psrphp/cms/content/create', ['model_id'=>$model['id'], 'copyfrom'=>$content['id']])}">复制</a>
                        </div>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <div class="mb-3">
        <form class="row gy-2 gx-3 align-items-center">
            <div class="col-auto">
                <button class="btn btn-secondary" type="button" id="fanxuan">全选/反选</button>
                <script>
                    $(document).ready(function() {
                        $("#fanxuan").on("click", function() {
                            $("#tablexx td :checkbox").each(function() {
                                $(this).prop("checked", !$(this).prop("checked"));
                            });
                        });
                    });
                </script>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger" id="delete">删除</button>
                <script>
                    $(document).ready(function() {
                        $("#delete").bind('click', function() {
                            if (confirm('确定删除吗？删除后不可恢复！')) {
                                var ids = [];
                                $.each($('#tablexx input:checkbox:checked'), function() {
                                    ids.push($(this).val());
                                });
                                $.ajax({
                                    type: "POST",
                                    url: "{echo $router->build('/psrphp/cms/content/delete')}",
                                    data: {
                                        model_id: "{$model.id}",
                                        ids: ids
                                    },
                                    dataType: "JSON",
                                    success: function(response) {
                                        if (response.errcode) {
                                            alert(response.message);
                                        } else {
                                            location.reload();
                                        }
                                    }
                                });
                            }
                        });
                    });
                </script>
            </div>

            <div class="col-auto">
                <div class="d-flex">
                    <div id="selcc">
                        <style>
                            #selcc>div>.mt-2>label {
                                display: none;
                            }

                            #selcc>div>.mt-2 {
                                margin-top: 0 !important;
                            }
                        </style>
                        <?php
                        echo new PsrPHP\Form\Field\Select('分类', 'category_name', '', $categorys);
                        ?>
                    </div>
                    <div>
                        <button class="btn btn-primary" type="button" id="inlineFormCustomSelect">移动</button>
                    </div>
                </div>
                <script>
                    $(function() {
                        $("#inlineFormCustomSelect").bind('click', function() {
                            var category_name = $("#selcc input")[0].value;
                            if (category_name >= 0) {
                                var ids = [];
                                $.each($('#tablexx input:checkbox:checked'), function() {
                                    ids.push($(this).val());
                                });
                                $.ajax({
                                    type: "POST",
                                    url: "{echo $router->build('/psrphp/cms/content/move')}",
                                    data: {
                                        model_id: "{$model.id}",
                                        ids: ids,
                                        category_name: category_name,
                                    },
                                    dataType: "JSON",
                                    success: function(response) {
                                        if (response.errcode) {
                                            alert(response.message);
                                        } else {
                                            location.reload();
                                        }
                                    }
                                });
                            }
                        });
                    });
                </script>
            </div>
        </form>
    </div>
    <nav class="mb-3">
        <ul class="pagination">
            {foreach $pagination as $v}
            {if $v=='...'}
            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">{$v}</a></li>
            {elseif isset($v['current'])}
            <li class="page-item active"><a class="page-link" href="javascript:void(0);">{$v.page}</a></li>
            {else}
            <li class="page-item"><a class="page-link" href="{echo $router->build('/psrphp/cms/content/index', array_merge($_GET, ['page'=>$v['page']]))}">{$v.page}</a></li>
            {/if}
            {/foreach}
        </ul>
    </nav>
    {else}
    {foreach $models as $vo}
    <a href="{echo $router->build('/psrphp/cms/content/index', ['model_id'=>$vo['id']])}">{$vo.title}</a>
    {/foreach}
    {/if}
</div>
{include common/footer@psrphp/admin}