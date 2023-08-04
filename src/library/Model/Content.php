<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class Content extends Item
{
    private static $instances = [];

    private $model_id;

    private function __construct(int $model_id, int $id, array $data = null)
    {
        $this->model_id = $model_id;

        if (is_null($data)) {
            Framework::execute(function (
                Db $db,
            ) use ($id, $model_id) {
                $model = Model::getInstance($model_id);
                $this->setData($db->get('psrphp_cms_content_' . $model->getData('name'), '*', [
                    'id' => $id,
                ]));
            });
        } else {
            $this->setData($data);
        }
    }

    public static function getInstance(int $model_id, int $id, array $data = null): self
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new self($model_id, $id, $data);
        }
        return self::$instances[$id];
    }

    public function getModelId(): int
    {
        return $this->model_id;
    }
}
