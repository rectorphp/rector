<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Use_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\Restoration\NameMatcher\ExistingClassesProvider;

/**
 * @see \Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector\RestoreFullyQualifiedNameRectorTest
 */
final class RestoreFullyQualifiedNameRector extends AbstractRector
{
    /**
     * @var ExistingClassesProvider
     */
    private $existingClassesProvider;

    public function __construct(ExistingClassesProvider $existingClassesProvider)
    {
        $this->existingClassesProvider = $existingClassesProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Restore accidentally shortened class names to its fully qualified form.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use ShortClassOnly;

class AnotherClass
{
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use App\Whatever\ShortClassOnly;

class AnotherClass
{
}
CODE_SAMPLE

                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Use_::class, Param::class];
    }

    /**
     * @param Use_|Param $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Use_) {
            return $this->refactoryUse($node);
        }

        if ($node instanceof Param) {
            return $this->refactorParam($node);
        }

        return null;
    }

    private function matchFullyQualifiedName(string $desiredShortName): ?string
    {
        foreach ($this->existingClassesProvider->provide() as $declaredClass) {
            $declaredShortClass = (string) Strings::after($declaredClass, '\\', -1);
            if ($declaredShortClass !== $desiredShortName) {
                continue;
            }

            return $declaredClass;
        }

        return null;
    }

    private function refactorParam(Param $param): ?Param
    {
        $name = $param->type;
        if (! $name instanceof Name) {
            return null;
        }

        $fullyQualifiedName = $this->resolveFullyQualifiedName($name);
        if ($fullyQualifiedName === null) {
            return null;
        }

        $param->type = new FullyQualified($fullyQualifiedName);
        return $param;
    }

    private function refactoryUse(Use_ $use): Use_
    {
        foreach ($use->uses as $useUse) {
            $name = $useUse->name;

            $fullyQualifiedName = $this->resolveFullyQualifiedName($name);
            if ($fullyQualifiedName === null) {
                continue;
            }

            $useUse->name = new Name($fullyQualifiedName);
        }

        return $use;
    }

    private function resolveFullyQualifiedName(Name $name)
    {
        if (count($name->parts) !== 1) {
            return null;
        }

        $resolvedName = $this->getName($name);
        if ($resolvedName === null) {
            return null;
        }

        if (ClassExistenceStaticHelper::doesClassLikeExist($resolvedName)) {
            return null;
        }

        $fullyQualifiedName = $this->matchFullyQualifiedName($resolvedName);
        if ($fullyQualifiedName === null) {
            return null;
        }

        return $fullyQualifiedName;
    }
}
