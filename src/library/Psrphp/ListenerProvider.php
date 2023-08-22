<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Psrphp;

use App\Psrphp\Admin\Model\MenuProvider;
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
use App\Psrphp\Cms\Http\Content\Index as ContentIndex;
use App\Psrphp\Cms\Http\Model\Index;
use App\Psrphp\Cms\Model\FieldProvider;
use App\Psrphp\Cms\Model\ModelCreaterProvider;
use PsrPHP\Framework\Listener;

class ListenerProvider extends Listener
{
    public function __construct()
    {
        $this->add(MenuProvider::class, function (
            MenuProvider $provider
        ) {
            $provider->add('模型管理', Index::class);
            $provider->add('内容管理', ContentIndex::class);
        });

        $this->add(ModelCreaterProvider::class, function (
            ModelCreaterProvider $provider,
        ) {
            $provider->add('base', '基本数据模型', function () {
            });
        });

        $this->add(FieldProvider::class, function (
            FieldProvider $provider
        ) {
            $provider->add(Text::class);
            $provider->add(Textarea::class);
            $provider->add(Datetime::class);
            $provider->add(Createtime::class);
            $provider->add(Updatetime::class);
            $provider->add(Date::class);
            $provider->add(Time::class);
            $provider->add(Number::class);
            $provider->add(Boolean::class);
            $provider->add(Code::class);
            $provider->add(WYSIWYG::class);
            $provider->add(Markdown::class);
            $provider->add(Pic::class);
            $provider->add(Pics::class);
            $provider->add(Files::class);
            $provider->add(Select::class);
            $provider->add(Checkbox::class);
        });
    }
}
