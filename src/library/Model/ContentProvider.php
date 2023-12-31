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

    public function select(int $model_id, array $filters = [], string $q = '', array $orders = [], int $page = 1, int $size = 10): array
    {
        $sql = '';

        $wheres = [];
        $binds = [];

        if (!$model = $this->db->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ])) {
            return [];
        }

        $fields = $this->db->select('psrphp_cms_field', '*', [
            'model_id' => $model_id,
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);

        foreach ($fields as $field) {
            $field = array_merge(json_decode($field['extra'], true), $field);
            if (isset($filters[$field['name']]) && $field['type']) {
                $tmp = $field['type']::buildFilterSql($field, $filters[$field['name']]);
                if (isset($tmp['where']) && $tmp['where']) {
                    $wheres[] = $tmp['where'];
                }
                if (isset($tmp['binds']) && $tmp['binds']) {
                    $binds = array_merge($binds, $tmp['binds']);
                }
            }
        }

        if (strlen($q)) {
            $orsqls = [];
            $orbinds = [];
            foreach ($fields as $field) {
                if (!$field['type']) {
                    continue;
                }
                if ($field['type']::isSearchable()) {
                    $orsqls[] = '`' . $field['name'] . '` like :' . $field['name'];
                    $orbinds[':' . $field['name']] = '%' . $q . '%';
                }
            }
            if ($orsqls) {
                $wheres[] = '(' . implode(' OR ', $orsqls) . ')';
                $binds = array_merge($binds, $orbinds);
            }
        }

        if ($wheres) {
            // $sql .= ' WHERE ' . $this->build($wheres);
            $sql .= ' WHERE ' . implode(' and ', $wheres);
        }

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

        $res = $this->db->select('psrphp_cms_content_' . $model['name'], '*', Medoo::raw($sql, $binds));
        return is_array($res) ? $res : [];
    }

    public function count(int $model_id, array $filters = [], string $q = ''): int
    {
        $sql = '';

        $wheres = [];
        $binds = [];

        if (!$model = $this->db->get('psrphp_cms_model', '*', [
            'id' => $model_id,
        ])) {
            return 0;
        }

        $fields = $this->db->select('psrphp_cms_field', '*', [
            'model_id' => $model_id,
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);

        foreach ($fields as $field) {
            $field = array_merge(json_decode($field['extra'], true), $field);
            if (isset($filters[$field['name']]) && $field['type']) {
                $tmp = $field['type']::buildFilterSql($field, $filters[$field['name']]);
                if (isset($tmp['where']) && $tmp['where']) {
                    $wheres[] = $tmp['where'];
                }
                if (isset($tmp['binds']) && $tmp['binds']) {
                    $binds = array_merge($binds, $tmp['binds']);
                }
            }
        }

        if (strlen($q)) {
            $orsqls = [];
            $orbinds = [];
            foreach ($fields as $field) {
                if (!$field['type']) {
                    continue;
                }
                if ($field['type']::isSearchable()) {
                    $orsqls[] = '`' . $field['name'] . '` like :' . $field['name'];
                    $orbinds[':' . $field['name']] = '%' . $q . '%';
                }
            }
            if ($orsqls) {
                $wheres[] = '(' . implode(' OR ', $orsqls) . ')';
                $binds = array_merge($binds, $orbinds);
            }
        }

        if ($wheres) {
            // $sql .= ' WHERE ' . $this->build($wheres);
            $sql .= ' WHERE ' . implode(' and ', $wheres);
        }

        $res = $this->db->count('psrphp_cms_content_' . $model['name'], Medoo::raw($sql, $binds));
        return $res === false ? 0 : $res;
    }

    private function build(array $where, string $type = 'and')
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
