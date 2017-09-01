<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

/**
 * Ref.: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 *
 * FrameworkBundle classes replaced by new ones
 *
 * @todo extract AbstractClassReplacerRector
 */
final class FrameworkBundleClassReplacementsRector extends AbstractRector
{
    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name) {
            return false;
        }

        $fqnName = $node->toString();

        if (! isset($this->getOldClassToNewClassMap()[$fqnName])) {
            return false;
        }

        return true;
    }

    /**
     * @param Name $node
     */
    public function refactor(Node $node): ?Node
    {
        $newName = $this->getNewName($node->toString());

        return new FullyQualified($newName);
    }

    /**
     * @var string[]
     */
    public function getOldClassToNewClassMap(): array
    {
        return [
            'Symfony\Bundle\FrameworkBundle\DependencyInjectino\Compiler\SerializerPass' => 'Symfony\Component\Serializer\DependencyInjection\SerializerPass'
        ];
    }

    private function getNewName(string $oldName): string
    {
        return $this->getOldClassToNewClassMap()[$oldName];
    }
}
