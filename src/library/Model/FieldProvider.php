<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class FieldProvider extends Provider
{
    private $model_id;

    private function __construct(int $model_id)
    {
        $this->model_id = $model_id;

        Framework::execute(function (
            Db $db
        ) use ($model_id) {
            foreach ($db->select('psrphp_cms_field', '*', [
                'model_id' => $model_id,
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC',
                ],
            ]) as $value) {
                $this->list[$value['id']] = Field::getInstance($value['id'], $value);
            }
        });
    }

    public static function getInstance(int $model_id): self
    {
        return new self($model_id);
    }

    public function getModelId(): int
    {
        return $this->model_id;
    }

    public function get($id): Field
    {
        return $this->list[$id];
    }

    public function has($id): bool
    {
        return isset($this->list[$id]);
    }
}
