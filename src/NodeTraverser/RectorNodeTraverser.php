<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Rector\Contract\Rector\PhpRectorInterface;

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
     * @return PhpRectorInterface[]
     */
    public function getRectors(): array
    {
        return $this->visitors;
    }
}
