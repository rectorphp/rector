<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObject;

use Rector\Core\Contract\Rector\RectorInterface;

final class RectorWithLineChange
{
    public function __construct(
        private RectorInterface $rector,
        private int $line
    ) {
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return get_class($this->rector);
    }

    public function getLine(): int
    {
        return $this->line;
    }
}
