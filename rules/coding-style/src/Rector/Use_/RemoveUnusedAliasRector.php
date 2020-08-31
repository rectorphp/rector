<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
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
     * @var array<string, string[]>
     */
    private $useNamesAliasToName = [];

    /**
     * @var string[]
     */
    private $resolvedDocPossibleAliases = [];

    /**
     * @var DocAliasResolver
     */
    private $docAliasResolver;

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
        UseManipulator $useManipulator,
        UseNameAliasToNameResolver $useNameAliasToNameResolver
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

        // lowercase
        $this->resolvedDocPossibleAliases = $this->lowercaseArray($this->resolvedDocPossibleAliases);

        $this->resolvedNodeNames = array_change_key_case($this->resolvedNodeNames, CASE_LOWER);
        $this->useNamesAliasToName = array_change_key_case($this->useNamesAliasToName, CASE_LOWER);

        foreach ($node->uses as $use) {
            if ($use->alias === null) {
                continue;
            }

            $lastName = $use->name->getLast();
            $lowercasedLastName = strtolower($lastName);

            /** @var string $aliasName */
            $aliasName = $this->getName($use->alias);
            if ($this->shouldSkip($lastName, $aliasName)) {
                continue;
            }

            // only last name is used → no need for alias
            if (isset($this->resolvedNodeNames[$lowercasedLastName])) {
                $use->alias = null;
                continue;
            }

            $this->refactorAliasName($aliasName, $lastName, $use);
        }

        return $node;
    }

    private function shouldSkipUse(Use_ $use): bool
    {
        return ! $this->hasUseAlias($use);
    }

    private function resolveSearchNode(Use_ $use): ?Node
    {
        $searchNode = $use->getAttribute(AttributeKey::PARENT_NODE);
        if ($searchNode !== null) {
            return $searchNode;
        }

        return $use->getAttribute(AttributeKey::NEXT_NODE);
    }

    /**
     * @param string[] $values
     * @return string[]
     */
    private function lowercaseArray(array $values): array
    {
        return array_map(function (string $value): string {
            return strtolower($value);
        }, $values);
    }

    private function shouldSkip(string $lastName, string $aliasName): bool
    {
        // PHP is case insensitive
        $loweredLastName = strtolower($lastName);
        $loweredAliasName = strtolower($aliasName);

        // both are used → nothing to remove
        if (isset($this->resolvedNodeNames[$loweredLastName], $this->resolvedNodeNames[$loweredAliasName])) {
            return true;
        }

        // part of some @Doc annotation
        return in_array($loweredAliasName, $this->resolvedDocPossibleAliases, true);
    }

    private function refactorAliasName(string $aliasName, string $lastName, UseUse $useUse): void
    {
        // only alias name is used → use last name directly

        $lowerAliasName = strtolower($aliasName);
        if (! isset($this->resolvedNodeNames[$lowerAliasName])) {
            return;
        }

        // keep to differentiate 2 aliases classes
        $lowerLastName = strtolower($lastName);
        if (count($this->useNamesAliasToName[$lowerLastName] ?? []) > 1) {
            return;
        }

        $this->renameNameNode($this->resolvedNodeNames[$lowerAliasName], $lastName);
        $useUse->alias = null;
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

            if ($parentNode instanceof StaticCall) {
                $this->renameStaticCall($lastName, $parentNode);
            }
        }
    }

    /**
     * @param Name|Identifier $usedNameNode
     */
    private function renameTraitUse(string $lastName, TraitUse $traitUse, Node $usedNameNode): void
    {
        foreach ($traitUse->traits as $key => $traitName) {
            if (! $this->areNamesEqual($traitName, $usedNameNode)) {
                continue;
            }

            $traitUse->traits[$key] = new Name($lastName);
        }
    }

    /**
     * @param Name|Identifier $usedNameNode
     */
    private function renameClass(string $lastName, Class_ $class, Node $usedNameNode): void
    {
        if ($class->name !== null && $this->areNamesEqual($class->name, $usedNameNode)) {
            $class->name = new Identifier($lastName);
        }

        if ($class->extends !== null && $this->areNamesEqual($class->extends, $usedNameNode)) {
            $class->extends = new Name($lastName);
        }

        foreach ($class->implements as $key => $implementNode) {
            if ($this->areNamesEqual($implementNode, $usedNameNode)) {
                $class->implements[$key] = new Name($lastName);
            }
        }
    }

    /**
     * @param Name|Identifier $usedNameNode
     */
    private function renameParam(string $lastName, Node $parentNode, Node $usedNameNode): void
    {
        if ($parentNode->type !== null && $this->areNamesEqual($parentNode->type, $usedNameNode)) {
            $parentNode->type = new Name($lastName);
        }
    }

    /**
     * @param Name|Identifier $usedNameNode
     */
    private function renameNew(string $lastName, Node $parentNode, Node $usedNameNode): void
    {
        if ($this->areNamesEqual($parentNode->class, $usedNameNode)) {
            $parentNode->class = new Name($lastName);
        }
    }

    /**
     * @param Name|Identifier $usedNameNode
     */
    private function renameClassMethod(string $lastName, ClassMethod $classMethod, Node $usedNameNode): void
    {
        if ($classMethod->returnType === null) {
            return;
        }

        if (! $this->areNamesEqual($classMethod->returnType, $usedNameNode)) {
            return;
        }

        $classMethod->returnType = new Name($lastName);
    }

    /**
     * @param Name|Identifier $usedNameNode
     */
    private function renameInterface(string $lastName, Interface_ $interface, Node $usedNameNode): void
    {
        foreach ($interface->extends as $key => $extendInterfaceName) {
            if (! $this->areNamesEqual($extendInterfaceName, $usedNameNode)) {
                continue;
            }

            $interface->extends[$key] = new Name($lastName);
        }
    }

    private function renameStaticCall(string $lastName, StaticCall $staticCall): void
    {
        $staticCall->class = new Name($lastName);
    }
}
