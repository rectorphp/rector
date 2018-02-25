<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Contract\Rector\RectorInterface;
use Rector\RectorBuilder\Contract\RectorProviderInterface;

final class RectorNodeTraverser extends NodeTraverser
{
    public function addRectorProvider(RectorProviderInterface $rectorProvider): void
    {
        foreach ($rectorProvider->provide() as $rector) {
            $this->addVisitor($rector);
        }
    }

    public function getRectorCount(): int
    {
        return count($this->visitors);
    }

    /**
     * @return RectorInterface[]|NodeTraverser[]
     */
    public function getRectors(): array
    {
        return $this->visitors;
    }
}
