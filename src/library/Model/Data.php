<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class Data extends Item
{
    private static $instances = [];

    private function __construct(int $data_id, array $data = null)
    {
        if (is_null($data)) {
            Framework::execute(function (
                Db $db,
            ) use ($data_id) {
                $this->data = $db->get('psrphp_cms_data', '*', [
                    'id' => $data_id,
                ]);
            });
        } else {
            $this->data = $data;
        }
    }

    public static function getInstance(int $data_id, array $data = null): self
    {
        if (!isset(self::$instances[$data_id])) {
            self::$instances[$data_id] = new self($data_id, $data);
        }
        return self::$instances[$data_id];
    }
}
