<?php declare(strict_types=1);

namespace Rector\Dispatcher;

use PhpParser\Node;
use Rector\Contract\Dispatcher\ReconstructorInterface;

final class NodeDispatcher
{
    /**
     * @var ReconstructorInterface[]
     */
    private $reconstructors;

    public function addReconstructor(ReconstructorInterface $reconstructor): void
    {
        $this->reconstructors[] = $reconstructor;
    }

    public function dispatch(Node $node): void
    {
        // todo: build hash map
        foreach ($this->reconstructors as $reconstructor) {
            if ($reconstructor->isCandidate($node)) {
                $reconstructor->reconstruct($node);
            }
        }
    }
}
