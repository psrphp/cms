<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Exception;
use Medoo\Medoo;
use PsrPHP\Database\Db;

class ContentProvider
{
    private $db;

    private $wheresql = '';
    private $wherebinds = [];

    public function __construct(
        Db $db
    ) {
        $this->db = $db;
    }

    public function select(int $model_id, array $category_names = [], array $filter = [], array $search = [], array $order = [], int $page = 1, int $size = 10): array
    {
        $res = [];

        $this->wheresql = '';
        $this->wherebinds = [];

        $model = $this->db->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ]);
        $this->renderWhere($model_id, $category_names, $filter, $search);
        $res['total'] = $this->db->count('psrphp_cms_content_' . $model['name'], Medoo::raw($this->wheresql, $this->wherebinds));

        $this->renderOrder($order);
        $this->renderLimit($page, $size);
        $res['contents'] = $this->db->select('psrphp_cms_content_' . $model['name'], '*', Medoo::raw($this->wheresql, $this->wherebinds));
        return $res;
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

        foreach ($this->db->select('psrphp_cms_field', '*', [
            'model_id' => $model_id,
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]) as $field) {
            $field = array_merge(json_decode($field['extra'], true), $field);
            if (isset($search[$field['name']])) {
                if ($res = $field['type']::onContentSearch($field, $search[$field['name']])) {
                    $likes[] = $res['sql'];
                    $this->wherebinds = array_merge($this->wherebinds, $res['binds']);
                }
            }
            if (isset($filter[$field['name']])) {
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

    private function renderOrder(array $order)
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
            $this->wheresql .= ' ORDER BY ' . implode(',', $orders);
        }
    }

    private function renderLimit(int $page, int $size)
    {
        $this->wheresql .= ' LIMIT :start, :size';
        $this->wherebinds[':start'] = ($page - 1) * $size;
        $this->wherebinds[':size'] = $size;
    }
}
