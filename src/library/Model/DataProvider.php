<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Countable;
use Exception;
use Iterator;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class DataProvider implements Iterator, Countable
{
    private $dict_id;

    private $list = [];
    private $keys = [];
    private $position;

    private function __construct(int $dict_id)
    {
        $this->dict_id = $dict_id;
        $this->position = 0;

        Framework::execute(function (
            Db $db
        ) {
            foreach ($db->select('psrphp_cms_data', '*', [
                'dict_id' => $this->dict_id,
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]) as $value) {
                $this->list[$value['id']] = Data::getInstance($value['id'])->setData($value);
            }
        });

        $this->keys = array_keys($this->list);
    }

    public static function getInstance(int $dict_id): self
    {
        return new self($dict_id);
    }

    public static function getCheckboxData(int $dict_id, int $value): iterable
    {
        $provider = self::getInstance($dict_id);
        $strs = array_reverse(str_split(decbin($value) . ''));
        foreach ($strs as $key => $value) {
            if ($value) {
                foreach ($provider as $v) {
                    if ($v['sn'] == $key) {
                        yield $v;
                    }
                }
            }
        }
    }

    public static function getSelectData(int $dict_id, int $value): iterable
    {
        $provider = self::getInstance($dict_id);

        foreach ($provider as $v) {
            if ($v['sn'] == $value) {
                foreach (self::getParentData($provider, $v) as $val) {
                    yield $val;
                }
                break;
            }
        }
    }

    public function get($key): Data
    {
        return $this->list[$key];
    }

    public function set($key, Data $value): self
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

    public function current(): Data
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

    public function __get($key): Data
    {
        return $this->list[$key];
    }

    public function __set($key, Data $val)
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
        if (!is_subclass_of($value, Data::class, false)) {
            throw new Exception('必须为：' . Data::class . ' 类型！');
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

    private static function getParentData(self $provider, Data $data): array
    {

        $res = [];
        foreach ($provider as $vo) {
            if ($vo['id'] == $data['pid']) {
                array_push($res, ...self::getParentData($provider, $vo));
            }
        }
        $res[] = $data;
        return $res;
    }
}
