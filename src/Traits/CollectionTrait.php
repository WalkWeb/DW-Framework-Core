<?php

declare(strict_types=1);

namespace WalkWeb\NW\Traits;

trait CollectionTrait
{
    public function key(): mixed
    {
        return key($this->elements);
    }

    public function next(): void
    {
        next($this->elements);
    }

    public function rewind(): void
    {
        reset($this->elements);
    }

    public function valid(): bool
    {
        return key($this->elements) !== null;
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
