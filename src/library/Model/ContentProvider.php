<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Exception;
use Medoo\Medoo;
use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class ContentProvider
{
    private $model;

    private $wheresql = '';
    private $wherebinds = [];

    private function __construct(int $model_id, array $category_names = null, array $filter = [],  array $qs = [])
    {
        $this->model = $this->getDb()->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ]);
        $this->renderWhere($model_id, $category_names, $filter, $qs);
    }

    public static function getInstance(int $model_id, array $category_names = null, array $filter = [], array $qs = []): self
    {
        return new self($model_id, $category_names, $filter, $qs);
    }

    public function getTotal()
    {
        return $this->getDb()->count('psrphp_cms_content_' . $this->model['name'], Medoo::raw($this->wheresql, $this->wherebinds));
    }

    public function select(array $order = [], int $page = 1, int $size = 10)
    {
        $sql = $this->wheresql;
        $binds = $this->wherebinds;
        $this->renderOrder($order, $sql, $binds);
        $this->renderLimit($page, $size, $sql, $binds);
        return $this->getDb()->select('psrphp_cms_content_' . $this->model['name'], '*', Medoo::raw($sql, $binds));
    }

    private function renderWhere(int $model_id, array $category_names = [], array $filter = [],  array $qs = [])
    {
        $where = [];
        $likes = [];

        if ($category_names) {
            $catn = [];
            foreach ($category_names as $vo) {
                if (!preg_match('/^[A-Za-z0-9_]+$/', $vo)) {
                    throw new Exception("参数[categorys_names]错误");
                }
                $key = ':catname_' . $vo;
                $catn[] = $key;
                $this->wherebinds[$key] = $vo;
            }
            $where[] = '`category_name` in (' . implode(',', $catn) . ')';
        }

        foreach ($this->getDb()->select('psrphp_cms_field', '*', [
            'model_id' => $model_id,
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]) as $field) {
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
                                    $where[] = '`' . $field['name'] . '`&' . $x . '>0';
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
                                    $where[] = '`' . $field['name'] . '`&' . $x . '>0';
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
                                    $where[] = '`' . $field['name'] . '`&' . $x . ' = ' . $x;
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
                            array_push($ids, ...$this->getSubDataIds($this->getDb()->select('psrphp_cms_data', '*', [
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
                        $where[] = '`' . $field['name'] . '` in (' . implode(',', $sns) . ')';
                    }
                    break;

                case 'text':
                case 'textarea':
                case 'code':
                case 'markdown':
                case 'editor':
                    if (isset($qs[$field['name']])) {
                        $likes[] = '`' . $field['name'] . '` like :' . $field['name'];
                        $this->wherebinds[':' . $field['name']] = $qs[$field['name']];
                    }
                    break;

                case 'int':
                case 'float':
                case 'date':
                case 'time':
                case 'datetime':
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
                        $where[] = '`' . $field['name'] . '` BETWEEN ' . $minkey . ' AND ' . $maxkey;
                        $this->wherebinds[$minkey] = $filter[$field['name']]['min'];
                        $this->wherebinds[$maxkey] = $filter[$field['name']]['max'];
                    } elseif (strlen($filter[$field['name']]['min'])) {
                        $where[] = '`' . $field['name'] . '`>=' . $minkey;
                        $this->wherebinds[$minkey] = $filter[$field['name']]['min'];
                    } elseif (strlen($filter[$field['name']]['max'])) {
                        $where[] = '`' . $field['name'] . '`<=' . $maxkey;
                        $this->wherebinds[$maxkey] = $filter[$field['name']]['max'];
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
            $this->wheresql = ' WHERE ' . implode(' AND ', $where);
        }
    }

    private function renderOrder(array $order, string &$string, array &$binds)
    {
        $orders = [];
        foreach ($order as $key => $value) {
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

    private function renderLimit(int $page, int $size, string &$string, array &$binds)
    {
        $string .= ' LIMIT :start, :size';
        $binds[':start'] = ($page - 1) * $size;
        $binds[':size'] = $size;
    }

    private function getSubDataIds($datas, $id): array
    {
        $res = [];
        foreach ($datas as $vo) {
            if ($vo['pid'] == $id) {
                array_push($res, ...$this->getSubDataIds($datas, $vo['id']));
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
