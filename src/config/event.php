<?php

use App\Psrphp\Cms\Model\Category;
use App\Psrphp\Cms\Model\CategoryProvider;

return [
    CategoryProvider::class => [
        function (
            CategoryProvider $provider
        ) {
            $provider->add(Category::getInstance('1', '测试栏目1', '2', 'groupxx'));
            $provider->add(Category::getInstance('3', '测试栏目3', '', 'groupxx'));
            $provider->add(Category::getInstance('4', '测试栏目4'));
            $provider->add(Category::getInstance('2', '测试栏目2'));
        }
    ],
];
