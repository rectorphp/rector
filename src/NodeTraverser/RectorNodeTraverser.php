<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Contract\Rector\PhpRectorInterface;
use Rector\Contract\Rector\RectorInterface;

final class RectorNodeTraverser extends NodeTraverser
{
    public function addRector(PhpRectorInterface $phpRector): void
    {
        $this->addVisitor($phpRector);
    }

    public function getRectorCount(): int
    {
        return count($this->visitors);
    }

    /**
     * @return RectorInterface[]
     */
    public function getRectors(): array
    {
        return $this->visitors;
    }
}
