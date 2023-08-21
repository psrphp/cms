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

return [
    ModelCreaterProvider::class => [
        function (
            ModelCreaterProvider $modelCreaterProvider,
        ) {
            $modelCreaterProvider->add('base', '基本数据模型', function () {
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
