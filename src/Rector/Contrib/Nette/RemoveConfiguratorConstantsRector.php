<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

final class RemoveConfiguratorConstantsRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if ($node instanceof ClassConstFetch) {
            if ((string) $node->class !== 'Nette\Configurator') {
                return false;
            }

            if (! in_array((string) $node->name, ['DEVELOPMENT', 'PRODUCTION'], true)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     */
    public function refactor($classConstFetchNode): void
    {
        dump($classConstFetchNode->name->name);
        die;

        $classConstFetchNode->name->name = $this->getNewConstantName();
    }

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.3;
    }
}
