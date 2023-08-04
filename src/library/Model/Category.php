<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

class Category extends Item
{
    private static $instances = [];

    private function __construct(string $name, string $title = null, string $parent = null, string $group = null)
    {
        $this->data['name'] = $name;
        $this->data['title'] = $title;
        $this->data['parent'] = $parent;
        $this->data['group'] = $group;
    }

    public static function getInstance(string $name, string $title = null, string $parent = null, string $group = null): self
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name, $title, $parent, $group);
        }
        return self::$instances[$name];
    }
}
