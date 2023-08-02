<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use ArrayAccess;
use Stringable;

class Category implements ArrayAccess, Stringable
{

    private $data = [];

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

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function __isset($key): bool
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    public function __toString()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}
