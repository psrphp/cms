<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Exception;
use PsrPHP\Framework\Framework;
use PsrPHP\Psr14\Event;

class CategoryProvider
{
    private static $instances = [];

    private $model_id;
    private $list = [];

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

    public function getModelId(): int
    {
        return $this->model_id;
    }

    public function add(string $name, string $title, string $parent = null, string $group = null): self
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new Exception("栏目名称只能由字母、数字、下划线组成");
        }
        $this->list[$name] = [
            'name' => $name,
            'title' => $title,
            'parent' => $parent,
            'group' => $group,
        ];
        return $this;
    }

    public function all(): array
    {
        return $this->list;
    }
}
