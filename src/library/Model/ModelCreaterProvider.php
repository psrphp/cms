<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Exception;
use PsrPHP\Framework\Framework;
use PsrPHP\Psr14\Event;

class ModelCreaterProvider
{
    private static $instance;

    private $list = [];

    private function __construct()
    {
        Framework::execute(function (
            Event $event
        ) {
            $event->dispatch($this);
        });
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function add(string $type, string $title, callable $action): self
    {
        $this->list[$type] = [
            'type' => $type,
            'title' => $title,
            'action' => $action,
        ];
        return $this;
    }

    public function create(string $type, array $args)
    {
        if (!isset($this->list[$type])) {
            throw new Exception('参数错误');
        }
        Framework::execute($this->list[$type]['action'], $args);
    }

    public function all(): array
    {
        return $this->list;
    }
}
