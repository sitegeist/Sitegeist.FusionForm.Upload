<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\Domain;

class CachedUploadFileCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var CachedUploadedFile[]
     */
    protected $files = [];

    public function __construct(CachedUploadedFile ... $files)
    {
        $this->files = $files;
    }

    public function count(): int
    {
        return count($this->files);
    }

    public function getAsArray(): array
    {
        return $this->files;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->files);
    }
}
