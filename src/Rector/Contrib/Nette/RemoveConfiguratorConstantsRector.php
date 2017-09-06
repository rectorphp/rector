<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Deprecation\SetNames;
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

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.3;
    }

    private function getClassNameFromClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        /** @var Node\Name\FullyQualified $fqnName */
        $fqnName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        return $fqnName->toString();
    }

    private function getDesiredClass(): string
    {
        return 'Nette\Configurator';
    }
}
