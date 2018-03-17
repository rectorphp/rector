<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class ServiceLocatorToDI extends AbstractRector
{
    public function __construct()
    {
    }

    public function isCandidate(Node $node): bool
    {
        return false;
    }

    public function refactor(Node $node): ?Node
    {
    }
}
