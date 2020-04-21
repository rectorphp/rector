<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Node\DocAliasResolver;
use Rector\CodingStyle\Node\UseManipulator;
use Rector\CodingStyle\Node\UseNameAliasToNameResolver;
use Rector\CodingStyle\ValueObject\NameAndParentValueObject;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\RemoveUnusedAliasRectorTest
 */
final class RemoveUnusedAliasRector extends AbstractRector
{
    /**
     * @var NameAndParentValueObject[][]
     */
    private $resolvedNodeNames = [];

    /**
     * @var string[][]
     */
    private $useNamesAliasToName = [];

    /**
     * @var DocAliasResolver
     */
    private $docAliasResolver;

    /**
     * @var string[]
     */
    private $resolvedDocPossibleAliases = [];

    /**
     * @var UseNameAliasToNameResolver
     */
    private $useNameAliasToNameResolver;

    /**
     * @var UseManipulator
     */
    private $useManipulator;

    public function __construct(
        DocAliasResolver $docAliasResolver,
        UseNameAliasToNameResolver $useNameAliasToNameResolver,
        UseManipulator $useManipulator
    ) {
        $this->docAliasResolver = $docAliasResolver;
        $this->useNameAliasToNameResolver = $useNameAliasToNameResolver;
        $this->useManipulator = $useManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes unused use aliases. Keep annotation aliases like "Doctrine\ORM\Mapping as ORM" to keep convention format',
            [
                new CodeSample(
                    <<<'PHP'
use Symfony\Kernel as BaseKernel;

class SomeClass extends BaseKernel
{
}
PHP
                    ,
                    <<<'PHP'
use Symfony\Kernel;

class SomeClass extends Kernel
{
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Use_::class];
    }

    /**
     * @param Use_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipUse($node)) {
            return null;
        }

        $searchNode = $this->resolveSearchNode($node);
        if ($searchNode === null) {
            return null;
        }

        $this->resolvedNodeNames = $this->useManipulator->resolveUsedNameNodes($searchNode);
        $this->resolvedDocPossibleAliases = $this->docAliasResolver->resolve($searchNode);

        $this->useNamesAliasToName = $this->useNameAliasToNameResolver->resolve($node);

        foreach ($node->uses as $use) {
            if ($use->alias === null) {
                continue;
            }

            $lastName = $use->name->getLast();

            /** @var string $aliasName */
            $aliasName = $this->getName($use->alias);

            if ($this->shouldSkip($lastName, $aliasName)) {
                continue;
            }

            // only last name is used → no need for alias
            if (isset($this->resolvedNodeNames[$lastName])) {
                $use->alias = null;
                continue;
            }

            $this->refactorAliasName($aliasName, $lastName, $use);
        }

        return $node;
    }

    private function shouldSkip(string $lastName, string $aliasName): bool
    {
        // both are used → nothing to remove
        if (isset($this->resolvedNodeNames[$lastName], $this->resolvedNodeNames[$aliasName])) {
            return true;
        }

        // part of some @Doc annotation
        return in_array($aliasName, $this->resolvedDocPossibleAliases, true);
    }

    /**
     * @param NameAndParentValueObject[] $usedNameNodes
     */
    private function renameNameNode(array $usedNameNodes, string $lastName): void
    {
        foreach ($usedNameNodes as $nameAndParent) {
            $parentNode = $nameAndParent->getParentNode();
            $usedName = $nameAndParent->getNameNode();

            if ($parentNode instanceof TraitUse) {
                $this->renameTraitUse($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Class_) {
                $this->renameClass($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Param) {
                $this->renameParam($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof New_) {
                $this->renameNew($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof ClassMethod) {
                $this->renameClassMethod($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Interface_) {
                $this->renameInterface($lastName, $parentNode, $usedName);
            }
        }
    }

    private function resolveSearchNode(Use_ $use): ?Node
    {
        $searchNode = $use->getAttribute(AttributeKey::PARENT_NODE);
        if ($searchNode !== null) {
            return $searchNode;
        }

        return $use->getAttribute(AttributeKey::NEXT_NODE);
    }

    private function renameClass(string $lastName, Class_ $class, $usedName): void
    {
        if ($class->name !== null && $this->areNamesEqual($class->name, $usedName)) {
            $class->name = new Identifier($lastName);
        }

        if ($class->extends !== null && $this->areNamesEqual($class->extends, $usedName)) {
            $class->extends = new Name($lastName);
        }

        foreach ($class->implements as $key => $implementNode) {
            if ($this->areNamesEqual($implementNode, $usedName)) {
                $class->implements[$key] = new Name($lastName);
            }
        }
    }

    private function renameTraitUse(string $lastName, TraitUse $traitUse, $usedName): void
    {
        foreach ($traitUse->traits as $key => $traitName) {
            if (! $this->areNamesEqual($traitName, $usedName)) {
                continue;
            }

            $traitUse->traits[$key] = new Name($lastName);
        }
    }

    private function renameParam(string $lastName, $parentNode, $usedName): void
    {
        if ($parentNode->type !== null && $this->areNamesEqual($parentNode->type, $usedName)) {
            $parentNode->type = new Name($lastName);
        }
    }

    private function renameNew(string $lastName, $parentNode, $usedName): void
    {
        if ($this->areNamesEqual($parentNode->class, $usedName)) {
            $parentNode->class = new Name($lastName);
        }
    }

    private function renameClassMethod(string $lastName, ClassMethod $classMethod, $usedName): void
    {
        if ($classMethod->returnType !== null && $this->areNamesEqual($classMethod->returnType, $usedName)) {
            $classMethod->returnType = new Name($lastName);
        }
    }

    private function renameInterface(string $lastName, Interface_ $parentNode, $usedName): void
    {
        foreach ($parentNode->extends as $key => $extendInterfaceName) {
            if ($this->areNamesEqual($extendInterfaceName, $usedName)) {
                $parentNode->extends[$key] = new Name($lastName);
            }
        }
    }

    private function refactorAliasName(string $aliasName, string $lastName, UseUse $useUse): void
    {
        // only alias name is used → use last name directly

        if (! isset($this->resolvedNodeNames[$aliasName])) {
            return;
        }

        // keep to differentiate 2 aliases classes
        if (isset($this->useNamesAliasToName[$lastName]) && count($this->useNamesAliasToName[$lastName]) > 1) {
            return;
        }

        $this->renameNameNode($this->resolvedNodeNames[$aliasName], $lastName);
        $useUse->alias = null;
    }

    private function shouldSkipUse(Use_ $use): bool
    {
        return ! $this->hasUseAlias($use);
    }

    private function hasUseAlias(Use_ $use): bool
    {
        foreach ($use->uses as $useUse) {
            if ($useUse->alias !== null) {
                return true;
            }
        }

        return false;
    }
}
