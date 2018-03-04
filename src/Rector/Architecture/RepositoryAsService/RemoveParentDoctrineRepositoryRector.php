<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class RemoveParentDoctrineRepositoryRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        if (! $node->extends) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Doctrine\ORM\EntityRepository') {
            return false;
        }

        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith($className, 'Repository');
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->extends = null;

        return $node;
    }
}
