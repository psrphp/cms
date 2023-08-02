<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;
use Stringable;

class ModelProvider implements Iterator, Countable, ArrayAccess, Stringable
{
    private $list = [];
    private $keys = [];
    private $position;

    private function __construct()
    {
        $this->position = 0;

        Framework::execute(function (
            Db $db
        ) {
            foreach ($db->select('psrphp_cms_model', '*') as $value) {
                $this->list[$value['id']] = Model::getInstance($value['id'])->setData($value);
            }
        });

        $this->keys = array_keys($this->list);
    }

    public static function getInstance(): self
    {
        return new self;
    }

    public function get($key): Model
    {
        return $this->list[$key];
    }

    public function set($key, Model $value): self
    {
        $this->list[$key] = $value;
        return $this;
    }

    public function has($key): bool
    {
        return isset($this->list[$key]);
    }

    public function delete($key): void
    {
        unset($this->list[$key]);
    }

    public function count(): int
    {
        return count($this->keys);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): Model
    {
        return $this->list[$this->keys[$this->position]];
    }

    public function key(): mixed
    {
        return $this->keys[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    public function __get($key): Model
    {
        return $this->list[$key];
    }

    public function __set($key, Model $val)
    {
        $this->list[$key] = $val;
    }

    public function __isset($key): bool
    {
        return isset($this->list[$key]);
    }

    public function __unset($key)
    {
        unset($this->list[$key]);
    }

    public function offsetGet($offset)
    {
        return $this->list[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!is_subclass_of($value, Model::class, false)) {
            throw new Exception('必须为：' . Model::class . ' 类型！');
        }
        if (is_null($offset)) {
            $this->list[] = $value;
        } else {
            $this->list[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->list[$offset]);
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->list[$offset]);
        }
    }

    public function __toString()
    {
        return json_encode($this->list);
    }
}
