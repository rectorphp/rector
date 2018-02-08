<?php declare(strict_types=1);

namespace Rector\Reporting;

final class FileDiff
{
    /**
     * @var string
     */
    private $diff;

    /**
     * @var string
     */
    private $file;

    public function __construct(string $file, string $diff)
    {
        $this->file = $file;
        $this->diff = $diff;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function getFile(): string
    {
        return $this->file;
    }
}
