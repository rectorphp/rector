<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\Rector\AbstractRector;

final class ReplaceParentRepositoryCallsByRepositoryPropertyRector extends AbstractRector
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    public function __construct(
        SmartClassReflector $smartClassReflector,
        PropertyFetchNodeFactory $propertyFetchNodeFactory
    ) {
        $this->smartClassReflector = $smartClassReflector;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        return in_array($node->name->toString(), $this->getEntityRepositoryPublicMethodNames(), true);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->var = $this->propertyFetchNodeFactory->createLocalWithPropertyName('repository');

        return $methodCallNode;
    }

    /**
     * @return string[]
     */
    private function getEntityRepositoryPublicMethodNames(): array
    {
        $entityRepositoryReflection = $this->smartClassReflector->reflect('Doctrine\ORM\EntityRepository');

        if ($entityRepositoryReflection !== null) {
            return array_keys($entityRepositoryReflection->getImmediateMethods());
        }

        return [];
    }
}
