<?php

declare(strict_types=1);

namespace App\Psrphp\Cms\Model;

use Countable;
use Iterator;
use Stringable;

abstract class Provider implements Iterator, Countable, Stringable
{
    protected array $list = [];

    public function count(): int
    {
        return count($this->list);
    }
    public function rewind(): void
    {
        reset($this->list);
    }
    public function current()
    {
        return current($this->list);
    }
    public function key()
    {
        return key($this->list);
    }
    public function next(): void
    {
        next($this->list);
    }
    public function valid(): bool
    {
        return key($this->list) !== null;
    }
    public function __toString()
    {
        return json_encode($this->list, JSON_UNESCAPED_UNICODE);
    }
}
