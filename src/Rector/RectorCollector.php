<?php declare(strict_types=1);

namespace Rector\Rector;

use Rector\Contract\Rector\RectorInterface;

final class RectorCollector
{
    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    public function addRector(RectorInterface $rector): void
    {
        // @todo: fix for multiple BuilderRector[] instances
        $this->rectors[get_class($rector)] = $rector;
    }

    public function getRectorCount(): int
    {
        return count($this->rectors);
    }

    /**
     * @return RectorInterface[]
     */
    public function getRectors(): array
    {
        return $this->rectors;
    }
}
