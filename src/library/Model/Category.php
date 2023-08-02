<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use ArrayAccess;
use Stringable;

class Category implements ArrayAccess, Stringable
{

    private $data = [];

    private static $instances = [];

    private function __construct(string $id, string $title = null, string $parent = null, string $group = null)
    {
        $this->data['id'] = $id;
        $this->data['title'] = $title;
        $this->data['parent'] = $parent;
        $this->data['group'] = $group;
    }

    public static function getInstance(string $id, string $title = null, string $parent = null, string $group = null): self
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new self($id, $title, $parent, $group);
        }
        return self::$instances[$id];
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
