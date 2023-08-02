<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Countable;
use Exception;
use Iterator;
use Medoo\Medoo;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class ContentProvider implements Iterator, Countable
{
    private $model_id;
    private $category_id;
    private $filter;
    private $order;
    private $q;
    private $page;
    private $size;

    private $list = [];
    private $keys = [];
    private $position;

    private function __construct(int $model_id, $category_id = null, array $filter = [], array $order = [], string $q = '', int $page = 1, int $size = 10)
    {
        $this->model_id = $model_id;
        $this->category_id = $category_id;
        $this->filter = $filter;
        $this->order = $order;
        $this->q = $q;
        $this->page = $page;
        $this->size = $size;

        $string = '';
        $binds = [];
        $this->renderWhere($string, $binds);
        $this->renderOrder($string, $binds);
        $this->renderLimit($string, $binds);

        foreach ($this->getDb()->select('psrphp_cms_content_' . Model::getInstance($this->model_id)->getData('name'), '*', Medoo::raw($string, $binds)) as $value) {
            $this->list[$value['id']] = Content::getInstance($this->model_id, $value['id'])->setData($value);
        }

        $this->position = 0;
        $this->keys = array_keys($this->list);
    }

    public static function getInstance(int $model_id, $category_id = null, array $filter = [], array $order = [], string $q = '', int $page = 1, int $size = 10): self
    {
        return new self($model_id, $category_id, $filter, $order, $q, $page, $size);
    }

    public function getTotal()
    {
        $model = Model::getInstance(intval($this->model_id));
        $string = '';
        $binds = [];
        $this->renderWhere($string, $binds);
        return $this->getDb()->count('psrphp_cms_content_' . $model['name'], Medoo::raw($string, $binds));
    }

    public function get($key): Content
    {
        return $this->list[$key];
    }

    public function set($key, Content $value): self
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

    public function current(): Content
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

    public function __get($key): Content
    {
        return $this->list[$key];
    }

    public function __set($key, Content $val)
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
        if (!is_subclass_of($value, Content::class, false)) {
            throw new Exception('必须为：' . Content::class . ' 类型！');
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

    private function renderWhere(string &$string, array &$binds)
    {
        $model = Model::getInstance(intval($this->model_id));
        $fieldProvider = FieldProvider::getInstance($model['id']);

        $where = [];

        $likes = [];

        if ($this->category_id) {
            if ($ids = $this->getSubCategory($this->category_id)) {
                $where[] = 'category_id in (' . implode(',', $ids) . ')';
            }
        }

        $filter = $this->filter;

        foreach ($fieldProvider as $field) {
            $extra = is_null($field['extra']) ? [] : json_decode($field['extra'], true);
            switch ($field['type']) {
                case 'checkbox':
                    switch ($extra['filter_type']) {
                        case '1':
                            if (isset($filter[$field['name']])) {
                                $tmp = $filter[$field['name']];
                                if (is_string($tmp) && strlen($tmp)) {
                                    $sn = $this->getDb()->get('psrphp_cms_data', 'sn', [
                                        'dict_id' => $extra['dict_id'],
                                        'value' => $tmp
                                    ]);
                                    $x = pow(2, $sn);
                                    $where[] = $field['name'] . ' & ' . $x . ' > 0';
                                }
                            }
                            break;
                        case '2':
                            if (isset($filter[$field['name']])) {
                                $tmp = $filter[$field['name']];
                                if ($tmp && is_array($tmp)) {
                                    $x = 0;
                                    foreach ($this->getDb()->select('psrphp_cms_data', 'sn', [
                                        'dict_id' => $extra['dict_id'],
                                        'value' => $tmp
                                    ]) as $sn) {
                                        $x += pow(2, $sn);
                                    }
                                    $where[] = $field['name'] . ' & ' . $x . ' > 0';
                                }
                            }
                            break;
                        case '3':
                            if (isset($filter[$field['name']])) {
                                $tmp = $filter[$field['name']];
                                if ($tmp && is_array($tmp)) {
                                    $x = 0;
                                    foreach ($this->getDb()->select('psrphp_cms_data', 'sn', [
                                        'dict_id' => $extra['dict_id'],
                                        'value' => $tmp
                                    ]) as $sn) {
                                        $x += pow(2, $sn);
                                    }
                                    $where[] = $field['name'] . '&' . $x . ' = ' . $x;
                                }
                            }
                            break;

                        default:
                            break;
                    }
                    break;

                case 'select':
                    if (isset($filter[$field['name']])) {
                        $values = (array)$filter[$field['name']];
                        $ids = [];
                        foreach ($values as $tmp) {
                            $id = $this->getDb()->get('psrphp_cms_data', 'id', [
                                'dict_id' => $extra['dict_id'],
                                'value' => (string)$tmp
                            ]);
                            array_push($ids, ...$this->getSub($this->getDb()->select('psrphp_cms_data', '*', [
                                'dict_id' => $extra['dict_id'],
                                'ORDER' => [
                                    'priority' => 'DESC',
                                    'id' => 'ASC',
                                ],
                            ]), $id));
                        }
                        $sns = $this->getDb()->select('psrphp_cms_data', 'sn', [
                            'dict_id' => $extra['dict_id'],
                            'id' => $ids,
                        ]);
                        $where[] = $field['name'] . ' in (' . implode(',', $sns) . ')';
                    }
                    break;

                case 'text':
                case 'textarea':
                case 'code':
                case 'markdown':
                case 'editor':
                    if (strlen($this->q)) {
                        $likes[] = $field['name'] . ' like :' . $field['name'];
                        $binds[':' . $field['name']] = '%' . $this->q . '%';
                    }
                    break;

                case 'int':
                case 'float':
                case 'date':
                case 'time':
                case 'datetime-local':
                    if (!isset($filter[$field['name']])) {
                        break;
                    }
                    $filter[$field['name']] = array_merge(['min' => '', 'max' => ''], $filter[$field['name']]);
                    if (is_null($filter[$field['name']]['min'])) {
                        $filter[$field['name']]['min'] = '';
                    }
                    if (is_null($filter[$field['name']]['max'])) {
                        $filter[$field['name']]['max'] = '';
                    }

                    $minkey = ':minkey_' . $field['name'];
                    $maxkey = ':maxkey_' . $field['name'];
                    if (strlen($filter[$field['name']]['min']) && strlen($filter[$field['name']]['max'])) {
                        $where[] = $field['name'] . ' BETWEEN ' . $minkey . ' AND ' . $maxkey;
                        $binds[$minkey] = $filter[$field['name']]['min'];
                        $binds[$maxkey] = $filter[$field['name']]['max'];
                    } elseif (strlen($filter[$field['name']]['min'])) {
                        $where[] = $field['name'] . ' >= ' . $minkey;
                        $binds[$minkey] = $filter[$field['name']]['min'];
                    } elseif (strlen($filter[$field['name']]['max'])) {
                        $where[] = $field['name'] . ' <= ' . $maxkey;
                        $binds[$maxkey] = $filter[$field['name']]['max'];
                    }
                    break;

                default:
                    break;
            }
        }

        if ($likes) {
            $where[] = '(' . implode(' OR ', $likes) . ')';
        }

        if ($where) {
            $string .= ' WHERE ' . implode(' AND ', $where);
        }
    }

    private function renderOrder(string &$string, array &$binds)
    {
        $orders = [];
        foreach ($this->order as $key => $value) {
            $value = strtolower($value);
            if (!preg_match('/^[A-Za-z0-9_]+$/', $key)) {
                throw new Exception("参数错误");
            }
            if (in_array($value, ['desc', 'asc'])) {
                $orders[] = '`' . $key . '` ' . $value;
            }
        }

        if ($orders) {
            $string .= ' ORDER BY ' . implode(',', $orders);
        }
    }

    private function renderLimit(string &$string, array &$binds)
    {
        $string .= ' LIMIT :start, :size';
        $binds[':start'] = ($this->page - 1) * $this->size;
        $binds[':size'] = $this->size;
    }

    private function getSub($datas, $id): array
    {
        $res = [];
        foreach ($datas as $vo) {
            if ($vo['pid'] == $id) {
                array_push($res, ...$this->getSub($datas, $vo['id']));
            }
        }
        $res[] = $id;
        return $res;
    }

    private function getSubCategory($id): array
    {

        $res = [];
        foreach (CategoryProvider::getInstance($this->model_id) as $vo) {
            if ($vo['parent'] == $id) {
                array_push($res, ...$this->getSubCategory($vo['id']));
            }
        }
        $res[] = $id;
        return $res;
    }

    private function getDb(): Db
    {
        return Framework::execute(function (
            Db $db,
        ): Db {
            return $db;
        });
    }
}
