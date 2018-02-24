<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Contract\Rector\RectorInterface;

final class RectorNodeTraverser extends NodeTraverser
{
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
