<?php

use App\Psrphp\Cms\Field\Boolean;
use App\Psrphp\Cms\Field\Checkbox;
use App\Psrphp\Cms\Field\Code;
use App\Psrphp\Cms\Field\Createtime;
use App\Psrphp\Cms\Field\Date;
use App\Psrphp\Cms\Field\Datetime;
use App\Psrphp\Cms\Field\Files;
use App\Psrphp\Cms\Field\Markdown;
use App\Psrphp\Cms\Field\Number;
use App\Psrphp\Cms\Field\Pic;
use App\Psrphp\Cms\Field\Pics;
use App\Psrphp\Cms\Field\Select;
use App\Psrphp\Cms\Field\Text;
use App\Psrphp\Cms\Field\Textarea;
use App\Psrphp\Cms\Field\Time;
use App\Psrphp\Cms\Field\Updatetime;
use App\Psrphp\Cms\Field\WYSIWYG;
use App\Psrphp\Cms\Model\FieldProvider;
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
                    'type' => Datetime::class,
                    'adminorder' => 1,
                    'adminfilter' => 1,
                    'adminlist' => 1,
                    'extra' => json_encode([]),
                ]);
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD create_time datetime DEFAULT CURRENT_TIMESTAMP COMMENT \'创建时间\'');

                $db->insert('psrphp_cms_field', [
                    'model_id' => $model['id'],
                    'title' => '更新时间',
                    'name' => 'update_time',
                    'system' => 1,
                    'type' => Datetime::class,
                    'adminorder' => 1,
                    'adminfilter' => 1,
                    'adminlist' => 1,
                    'extra' => json_encode([]),
                ]);
                $db->query('ALTER TABLE <psrphp_cms_content_' . $model['name'] . '> ADD update_time datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'更新时间\'');
            });
        }
    ],
    FieldProvider::class => [
        function (
            FieldProvider $fieldProvider
        ) {
            $fieldProvider->add(Text::class);
            $fieldProvider->add(Textarea::class);
            $fieldProvider->add(Datetime::class);
            $fieldProvider->add(Createtime::class);
            $fieldProvider->add(Updatetime::class);
            $fieldProvider->add(Date::class);
            $fieldProvider->add(Time::class);
            $fieldProvider->add(Number::class);
            $fieldProvider->add(Boolean::class);
            $fieldProvider->add(Code::class);
            $fieldProvider->add(WYSIWYG::class);
            $fieldProvider->add(Markdown::class);
            $fieldProvider->add(Pic::class);
            $fieldProvider->add(Pics::class);
            $fieldProvider->add(Files::class);
            $fieldProvider->add(Select::class);
            $fieldProvider->add(Checkbox::class);
        }
    ],
];
