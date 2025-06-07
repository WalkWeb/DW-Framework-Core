<?php

declare(strict_types=1);

namespace WalkWeb\NW\Loader;

use Countable;
use Iterator;
use WalkWeb\NW\Traits\CollectionTrait;

class ImageCollection implements Iterator, Countable
{
    use CollectionTrait;

    /** @var Image[] */
    private array $elements;

    private int $totalSize = 0;

    public function add(Image $image): void
    {
        $this->elements[] = $image;
        $this->totalSize += $image->getSize();
    }

    public function current(): Image
    {
        return current($this->elements);
    }

    public function getTotalSize(): int
    {
        return $this->totalSize;
    }
}
