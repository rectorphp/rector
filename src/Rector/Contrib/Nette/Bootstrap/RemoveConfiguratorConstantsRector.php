<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Bootstrap;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class RemoveConfiguratorConstantsRector extends AbstractRector
{
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
        $constantName = (string) $classConstFetchNode->name;

        $originalConstantValue = strtolower($constantName);

        return new String_($originalConstantValue);
    }

    private function getClassNameFromClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        $fqnName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($fqnName === null && $classConstFetchNode->class instanceof Variable) {
            return (string) $classConstFetchNode->class->name;
        }

        return $fqnName->toString();
    }

    private function getDesiredClass(): string
    {
        return 'Nette\Configurator';
    }
}
