<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Contract\Rector\PhpRectorInterface;

final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @param PhpRectorInterface[] $phpRectors
     */
    public function __construct(array $phpRectors = [])
    {
        foreach ($phpRectors as $phpRector) {
            $this->addVisitor($phpRector);
        }
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
