<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Bootstrap;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

final class RemoveConfiguratorConstantsRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        $className = $this->getClassNameFromClassConstFetch($node);

        if ($className !== $this->getDesiredClass()) {
            return false;
        }

        return in_array((string) $node->name, ['DEVELOPMENT', 'PRODUCTION'], true);
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     */
    public function refactor(Node $classConstFetchNode): ?Node
    {
        /** @var Identifier $constantName */
        $constantName = (string) $classConstFetchNode->name;

        $originalConstantValue = $constantName->toLowerString();

        return $this->nodeFactory->createString($originalConstantValue);
    }

    private function getClassNameFromClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        /** @var FullyQualified|null $fqnName */
        $fqnName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($fqnName === null && $classConstFetchNode->class instanceof Variable) {
            return (string) $classConstFetchNode->class->name;
        }

        if ($fqnName !== null) {
            return $fqnName->toString();
        }
    }

    private function getDesiredClass(): string
    {
        return 'Nette\Configurator';
    }
}
