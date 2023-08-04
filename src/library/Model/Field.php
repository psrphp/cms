<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class Field extends Item
{
    private static $instances = [];

    private function __construct(int $field_id, array $data = null)
    {
        if (is_null($data)) {
            Framework::execute(function (
                Db $db,
            ) use ($field_id) {
                $this->setData($db->get('psrphp_cms_field', '*', [
                    'id' => $field_id,
                ]));
            });
        } else {
            $this->setData($data);
        }
    }

    public static function getInstance(int $field_id, array $data = null): self
    {
        if (!isset(self::$instances[$field_id])) {
            self::$instances[$field_id] = new self($field_id, $data);
        }
        return self::$instances[$field_id];
    }
}
