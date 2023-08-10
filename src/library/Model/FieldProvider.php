<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use App\Psrphp\Cms\Field\FieldInterface;
use Exception;
use PsrPHP\Framework\Framework;
use PsrPHP\Psr14\Event;

class FieldProvider
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
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add(string $type): self
    {
        if (!is_a($type, FieldInterface::class, true)) {
            throw new Exception('$type 必须为：' . FieldInterface::class);
        }
        $this->list[] = $type;
        return $this;
    }

    public function all(): array
    {
        return $this->list;
    }
}
