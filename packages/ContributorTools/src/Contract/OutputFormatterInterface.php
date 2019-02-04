<?php declare(strict_types=1);

namespace Rector\ContributorTools\Contract;

use Rector\Contract\Rector\RectorInterface;

interface OutputFormatterInterface
{
    public function getName(): string;

    /**
     * @param RectorInterface[] $genericRectors
     * @param RectorInterface[] $packageRectors
     */
    public function format(array $genericRectors, array $packageRectors): void;
}
