<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use ArrayAccess;
use Stringable;

abstract class Item implements ArrayAccess, Stringable
{
    private array $data;

    public function getData(string $field = null, $default = null)
    {
        if (is_null($field)) {
            return $this->data;
        } else {
            return isset($this->data[$field]) ? $this->data[$field] : $default;
        }
    }

    protected function setData(array $data)
    {
        $this->data = $data;
    }

    public function exist(): bool
    {
        return !is_null($this->data);
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
