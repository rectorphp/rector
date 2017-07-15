<?php declare(strict_types=1);

namespace Rector\Contract\Dispatcher;

use PhpParser\Node;

interface ReconstructorInterface
{
    public function isCandidate(Node $node): bool;

    public function reconstruct(Node $classNode): void;
}
