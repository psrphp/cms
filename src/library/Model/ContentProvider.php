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

    private function __construct(int $model_id, array $category_names = null, array $filter = [],  array $search = [])
    {
        $this->model = $this->getDb()->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ]);
        $this->renderWhere($model_id, $category_names, $filter, $search);
    }

    public static function getInstance(int $model_id, array $category_names = null, array $filter = [], array $search = []): self
    {
        return new self($model_id, $category_names, $filter, $search);
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

    private function renderWhere(int $model_id, array $category_names = [], array $filter = [],  array $search = [])
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
            if (isset($search[$field['name']])) {
                if ($res = $field['type']::onContentSearch($field, $search[$field['name']])) {
                    $likes[] = $res['sql'];
                    $this->wherebinds = array_merge($this->wherebinds, $res['binds']);
                }
            }
            if (isset($filter[$field['name']])) {
                $field['type']::onContentFilter($field, $filter[$field['name']]);
                if ($res = $field['type']::onContentFilter($field, $filter[$field['name']])) {
                    $where[] = $res['sql'];
                    $this->wherebinds = array_merge($this->wherebinds, $res['binds']);
                }
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
        foreach ($order as $key => $vo) {
            $vo = strtolower($vo);
            if (!preg_match('/^[A-Za-z0-9_]+$/', $key)) {
                throw new Exception("参数错误");
            }
            if (in_array($vo, ['desc', 'asc'])) {
                $orders[] = '`' . $key . '` ' . $vo;
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

    private function getDb(): Db
    {
        return Framework::execute(function (
            Db $db,
        ): Db {
            return $db;
        });
    }
}
