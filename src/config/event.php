<?php

use App\Psrphp\Cms\Model\ModelCreaterProvider;
use PsrPHP\Database\Db;

return [
    ModelCreaterProvider::class => [
        function (
            ModelCreaterProvider $modelCreaterProvider,
        ) {
            $modelCreaterProvider->add('normal', '基本数据模型', function (
                Db $db,
                $model,
            ) {
                $db->insert('psrphp_cms_field', [
                    'model_id' => $model['id'],
                    'title' => '创建时间',
                    'name' => 'create_time',
                    'system' => 1,
                    'type' => 'datetime',
                    'adminorder' => 1,
                    'adminfilter' => 1,
                    'adminlist' => 1,
                ]);
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD create_time datetime DEFAULT CURRENT_TIMESTAMP COMMENT \'创建时间\'');

                $db->insert('psrphp_cms_field', [
                    'model_id' => $model['id'],
                    'title' => '更新时间',
                    'name' => 'update_time',
                    'system' => 1,
                    'type' => 'datetime',
                    'adminorder' => 1,
                    'adminfilter' => 1,
                    'adminlist' => 1,
                ]);
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD update_time datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'更新时间\'');
            });
        }
    ],
];
