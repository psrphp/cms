<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

class Category extends Item
{
    private static $instances = [];

    private function __construct(string $name, string $title, string $parent = null, string $group = null)
    {
        $this->setData([
            'name' => $name,
            'title' => $title,
            'parent' => $parent,
            'group' => $group,
        ]);
    }

    public static function getInstance(string $name, string $title, string $parent = null, string $group = null): self
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name, $title, $parent, $group);
        }
        return self::$instances[$name];
    }
}
