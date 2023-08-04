<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use PsrPHP\Framework\Framework;
use PsrPHP\Psr14\Event;

class CategoryProvider extends Provider
{
    private static $instances = [];

    private $model_id;

    private function __construct(int $model_id)
    {
        $this->model_id = $model_id;

        Framework::execute(function (
            Event $event
        ) {
            $event->dispatch($this);
        });
    }

    public static function getInstance(int $model_id): self
    {
        if (!isset(self::$instances[$model_id])) {
            self::$instances[$model_id] = new self($model_id);
        }
        return self::$instances[$model_id];
    }

    public function getModel(): Model
    {
        return Model::getInstance($this->model_id);
    }

    public function add(Category $category)
    {
        return $this->list[$category['name']] = $category;
    }

    public function get($key): Category
    {
        return $this->list[$key];
    }

    public function has($key): bool
    {
        return isset($this->list[$key]);
    }

    public function delete($key): void
    {
        unset($this->list[$key]);
    }
}
