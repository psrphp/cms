<?php

use App\Psrphp\Cms\Http\Dict\Index as DictIndex;
use App\Psrphp\Cms\Http\Model\Index;

return [
    'menus' => [[
        'title' => '模型管理',
        'node' => Index::class,
    ], [
        'title' => '数据源管理',
        'node' => DictIndex::class,
    ]]
];
