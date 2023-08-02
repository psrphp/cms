<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Countable;
use Iterator;
use PsrPHP\Framework\Framework;
use PsrPHP\Psr14\Event;

class CategoryProvider implements Iterator, Countable
{
    private static $instances = [];
    private $model_id;

    private $container = [];
    private $keys = [];
    private $position;

    private function __construct(int $model_id)
    {
        $this->model_id = $model_id;
        $this->position = 0;

        Framework::execute(function (
            Event $event
        ) {
            $event->dispatch($this);
        });

        $this->keys = array_keys($this->container);
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
        return $this->container[$category['name']] = $category;
    }

    public function get($key): Category
    {
        return $this->container[$key];
    }

    public function has($key): bool
    {
        return isset($this->container[$key]);
    }

    public function delete($key): void
    {
        unset($this->container[$key]);
    }

    public function count(): int
    {
        return count($this->keys);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): Category
    {
        return $this->container[$this->keys[$this->position]];
    }

    public function key(): mixed
    {
        return $this->keys[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }
}
