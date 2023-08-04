<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Database\Db;
use PsrPHP\Framework\Framework;

class Content extends Item
{
    private $model_id;
    private $id;
    private static $instances = [];

    private function __construct(int $model_id, int $id, array $data = null)
    {
        $this->model_id = $model_id;
        $this->id = $id;
        if (is_null($data)) {
            Framework::execute(function (
                Db $db,
            ) {
                $this->data = $db->get('psrphp_cms_content_' . $this->getModel()->getData('name'), '*', [
                    'id' => $this->id,
                ]);
            });
        } else {
            $this->data = $data;
        }
    }

    public static function getInstance(int $model_id, int $id, array $data = null): self
    {
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new self($model_id, $id, $data);
        }
        return self::$instances[$id];
    }

    public function getModel(): Model
    {
        return Model::getInstance($this->model_id);
    }

    public function getCategory(): Category
    {
        return CategoryProvider::getInstance($this->model_id)[$this->getData('category_name')];
    }
}
