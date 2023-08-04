<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class DataProvider extends Provider
{
    private $dict_id;

    private function __construct(int $dict_id)
    {
        $this->dict_id = $dict_id;

        Framework::execute(function (
            Db $db
        ) use ($dict_id) {
            foreach ($db->select('psrphp_cms_data', '*', [
                'dict_id' => $dict_id,
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]) as $value) {
                $this->list[$value['id']] = Data::getInstance($value['id'], $value);
            }
        });
    }

    public static function getInstance(int $dict_id): self
    {
        return new self($dict_id);
    }

    public function getDictId(): int
    {
        return $this->dict_id;
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
