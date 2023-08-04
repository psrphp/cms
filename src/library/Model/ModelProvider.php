<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class ModelProvider extends Provider
{
    private function __construct()
    {
        Framework::execute(function (
            Db $db
        ) {
            foreach ($db->select('psrphp_cms_model', '*') as $value) {
                $this->list[$value['id']] = Model::getInstance($value['id'], $value);
            }
        });
    }

    public static function getInstance(): self
    {
        return new self;
    }

    public function get($id): Model
    {
        return $this->list[$id];
    }

    public function has($id): bool
    {
        return isset($this->list[$id]);
    }
}
