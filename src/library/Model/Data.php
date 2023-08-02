<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use ArrayAccess;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;
use Stringable;

class Data implements ArrayAccess, Stringable
{
    private $data_id;
    private $data;
    private static $instances = [];

    private function __construct(int $data_id)
    {
        $this->data_id = $data_id;
    }

    public static function getInstance(int $data_id): self
    {
        if (!isset(self::$instances[$data_id])) {
            self::$instances[$data_id] = new self($data_id);
        }
        return self::$instances[$data_id];
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getData(string $field = null, $default = null)
    {
        if (!$this->data) {
            Framework::execute(function (
                Db $db,
            ) {
                $this->data = $db->get('psrphp_cms_data', '*', [
                    'id' => $this->data_id,
                ]);
            });
        }
        if (is_null($field)) {
            return $this->data;
        } else {
            return isset($this->data[$field]) ? $this->data[$field] : $default;
        }
    }

    public function __get($key)
    {
        return $this->getData($key);
    }

    public function __set($key, $val)
    {
        $this->getData();
        $this->data[$key] = $val;
    }

    public function __isset($key): bool
    {
        $this->getData();
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        $this->getData();
        unset($this->data[$key]);
    }

    public function offsetGet($offset)
    {
        $this->getData();
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->getData();
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        $this->getData();
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
        return $this->getData('title');
    }
}
