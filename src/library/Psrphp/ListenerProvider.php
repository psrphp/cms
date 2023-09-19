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
use App\Psrphp\Cms\Field\Virtual;
use App\Psrphp\Cms\Field\WYSIWYG;
use App\Psrphp\Cms\Http\Content\Index;
use App\Psrphp\Cms\Http\Dict\Index as DictIndex;
use App\Psrphp\Cms\Http\Model\Index as ModelIndex;
use App\Psrphp\Cms\Model\FieldProvider;
use App\Psrphp\Cms\Model\ModelCreaterProvider;
use Psr\EventDispatcher\ListenerProviderInterface;
use PsrPHP\Framework\Framework;

class ListenerProvider implements ListenerProviderInterface
{
    public function getListenersForEvent(object $event): iterable
    {
        if (is_a($event, MenuProvider::class)) {
            yield function () use ($event) {
                Framework::execute(function (
                    MenuProvider $provider
                ) {
                    $provider->add('内容管理', Index::class);
                    $provider->add('模型管理', ModelIndex::class);
                    $provider->add('数据字典', DictIndex::class);
                }, [
                    MenuProvider::class => $event,
                ]);
            };
        }
        if (is_a($event, ModelCreaterProvider::class)) {
            yield function () use ($event) {
                Framework::execute(function (
                    ModelCreaterProvider $provider,
                ) {
                    $provider->add('base', '基本数据模型', function () {
                    });
                }, [
                    ModelCreaterProvider::class => $event,
                ]);
            };
        }
        if (is_a($event, FieldProvider::class)) {
            yield function () use ($event) {
                Framework::execute(function (
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
                    $provider->add(Virtual::class);
                }, [
                    FieldProvider::class => $event,
                ]);
            };
        }
    }
}
