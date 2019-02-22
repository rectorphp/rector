<?php declare(strict_types=1);

namespace Rector\ContributorTools\Contract\OutputFormatter;

use Rector\Contract\Rector\RectorInterface;

interface DumpRectorsOutputFormatterInterface
{
    public function getName(): string;

    /**
     * @param RectorInterface[] $genericRectors
     * @param RectorInterface[] $packageRectors
     */
    public function format(array $genericRectors, array $packageRectors): void;
}
