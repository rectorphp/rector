<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\Class_\Property;
use Rector\Builder\PropertyBuilder;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class RemoveParentDoctrineRepositoryRector extends AbstractRector
{
    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;

    public function __construct(PropertyBuilder $propertyBuilder)
    {
        $this->propertyBuilder = $propertyBuilder;
    }

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

        $property = Property::createFromNameAndTypes('repository', ['Doctrine\ORM\EntityRepository']);
        $this->propertyBuilder->addPropertyToClass($node, $property);

        //return $this->propertyFetchNodeFactory->createLocalWithPropertyName('repository');
        // add it  to constuctor

        return $node;
    }
}
