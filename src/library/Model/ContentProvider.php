<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Exception;
use Medoo\Medoo;
use PsrPHP\Database\Db;

class ContentProvider
{
    private $db;

    public function __construct(
        Db $db
    ) {
        $this->db = $db;
    }

    public function select(int $model_id, array $category_names = [], array $filters = [], array $orders = [], int $page = 1, int $size = 10): array
    {
        $res = [];
        $sql = '';

        $wheres = [];
        $binds = [];

        $model = $this->db->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ]);

        if ($category_names) {
            $catn = [];
            foreach ($category_names as $vo) {
                if (!preg_match('/^[A-Za-z0-9_]+$/', $vo)) {
                    throw new Exception("参数[categorys_names]错误");
                }
                $key = ':catname_' . $vo;
                $binds[$key] = $vo;
                $catn[] = $key;
            }
            $wheres[] = '`category_name` in (' . implode(',', $catn) . ')';
        }

        foreach ($this->db->select('psrphp_cms_field', '*', [
            'model_id' => $model_id,
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]) as $field) {
            $field = array_merge(json_decode($field['extra'], true), $field);
            if (isset($filters[$field['name']])) {
                $tmp = $field['type']::buildFilterSql($field, $filters[$field['name']]);
                if (isset($tmp['where']) && $tmp['where']) {
                    $wheres = array_merge_recursive($wheres, $tmp['where']);
                }
                if (isset($tmp['binds']) && $tmp['binds']) {
                    $binds = array_merge($binds, $tmp['binds']);
                }
            }
        }

        // var_dump($wheres);
        // die;
        if ($wheres) {
            $sql .= ' WHERE ' . $this->build($wheres);
        }

        // var_dump($binds);
        // var_dump($wheres);
        // echo $this->db->debug()->count('psrphp_cms_content_' . $model['name'], Medoo::raw($sql, $binds));
        // die;

        $res['total'] = $this->db->count('psrphp_cms_content_' . $model['name'], Medoo::raw($sql, $binds));

        $tmps = [];
        foreach ($orders as $key => $vo) {
            $vo = strtolower($vo);
            if (!preg_match('/^[A-Za-z0-9_]+$/', $key)) {
                throw new Exception("参数错误");
            }
            if (in_array($vo, ['desc', 'asc'])) {
                $tmps[] = '`' . $key . '` ' . $vo;
            }
        }
        if ($tmps) {
            $sql .= ' ORDER BY ' . implode(',', $tmps);
        }

        $sql .= ' LIMIT :start, :size';
        $binds[':start'] = ($page - 1) * $size;
        $binds[':size'] = $size;

        $res['contents'] = $this->db->select('psrphp_cms_content_' . $model['name'], '*', Medoo::raw($sql, $binds));
        return $res;
    }

    public function build(array $where, string $type = 'and')
    {
        if (isset($where['and'])) {
            $and = $where['and'];
            unset($where['and']);
            if ($tmp = $this->build($and, 'and')) {
                $where[] = $tmp;
            }
        }
        if (isset($where['or'])) {
            $or = $where['or'];
            unset($where['or']);
            if ($tmp = $this->build($or, 'or')) {
                $where[] = $tmp;
            }
        }
        if ($where) {
            return '(' . implode(' ' . $type . ' ', $where) . ')';
        }
    }
}
