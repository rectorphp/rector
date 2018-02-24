<?php declare(strict_types=1);

namespace Rector\RectorBuilder\Contract;

use PhpParser\NodeVisitor;
use Rector\Contract\Rector\RectorInterface;

interface RectorProviderInterface
{
    /**
     * @return NodeVisitor
     */
    public function provide(): RectorInterface;
}
