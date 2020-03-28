<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObject;

use Rector\Core\Contract\Rector\RectorInterface;

final class RectorWithFileAndLineChange
{
    /**
     * @var RectorInterface
     */
    private $rector;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $realPath;

    public function __construct(RectorInterface $rector, string $realPath, int $line)
    {
        $this->rector = $rector;
        $this->line = $line;
        $this->realPath = $realPath;
    }

    public function getRectorDefinitionsDescription(): string
    {
        return $this->rector->getDefinition()->getDescription();
    }

    public function getRectorClass(): string
    {
        return get_class($this->rector);
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }
}
