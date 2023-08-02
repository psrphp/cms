<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use ArrayAccess;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;
use Stringable;

class Content implements ArrayAccess, Stringable
{
    private $model_id;
    private $id;
    private $data;
    private static $instances = [];

    private function __construct(int $model_id, int $id)
    {
        $this->model_id = $model_id;
        $this->id = $id;
    }

    public static function getInstance(int $model_id, int $id): self
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new self($model_id, $id);
        }
        return self::$instances[$id];
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
                $this->data = $db->get('psrphp_cms_content_' . $this->getModel()->getData('name'), '*', [
                    'id' => $this->id,
                ]);
            });
        }
        if (is_null($field)) {
            return $this->data;
        } else {
            return isset($this->data[$field]) ? $this->data[$field] : $default;
        }
    }

    public function getModel(): Model
    {
        return Model::getInstance($this->model_id);
    }

    public function getCategory(): Category
    {
        return CategoryProvider::getInstance($this->model_id)[$this->getData('category_name')];
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
        return json_encode($this->getData(), JSON_UNESCAPED_UNICODE);
    }
}
