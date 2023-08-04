<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class Model extends Item
{
    private static $instances = [];

    private function __construct(int $model_id, array $data = null)
    {
        if (is_null($data)) {
            $this->data = Framework::execute(function (
                Db $db,
            ) use ($model_id): array {
                return $db->get('psrphp_cms_model', '*', [
                    'id' => $model_id,
                ]);
            });
        } else {
            $this->data = $data;
        }
    }

    public static function getInstance(int $model_id, array $data = null): self
    {
        if (!isset(self::$instances[$model_id])) {
            self::$instances[$model_id] = new self($model_id, $data);
        }
        return self::$instances[$model_id];
    }

    public function getFieldProvider(): FieldProvider
    {
        return FieldProvider::getInstance($this->data['model_id']);
    }
}
