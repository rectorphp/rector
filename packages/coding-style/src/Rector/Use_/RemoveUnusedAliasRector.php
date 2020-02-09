<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Use_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitor\NameResolver;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Imports\ShortNameResolver;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\RemoveUnusedAliasRectorTest
 */
final class RemoveUnusedAliasRector extends AbstractRector
{
    /**
     * @var Node[][][]
     */
    private $resolvedNodeNames = [];

    /**
     * @var string[]
     */
    private $resolvedDocPossibleAliases = [];

    /**
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    /**
     * @var string[][]
     */
    private $useNamesAliasToName = [];

    public function __construct(ShortNameResolver $shortNameResolver)
    {
        $this->shortNameResolver = $shortNameResolver;
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
        $this->resolvedNodeNames = [];
        $this->resolveUsedNameNodes($node);

        $this->collectUseNamesAliasToName($node);

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

    private function resolveUsedNameNodes(Use_ $node): void
    {
        $searchNode = $this->resolveSearchNode($node);
        if ($searchNode === null) {
            return;
        }

        $this->resolveUsedNames($searchNode);
        $this->resolveUsedClassNames($searchNode);
        $this->resolveTraitUseNames($searchNode);

        $this->resolvedDocPossibleAliases = $this->resolveDocPossibleAliases($searchNode);
    }

    private function collectUseNamesAliasToName(Use_ $use): void
    {
        $this->useNamesAliasToName = [];

        $shortNames = $this->shortNameResolver->resolveForNode($use);
        foreach ($shortNames as $alias => $useImport) {
            $shortName = $this->getShortName($useImport);
            if ($shortName === $alias) {
                continue;
            }

            $this->useNamesAliasToName[$shortName][] = $alias;
        }
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
     * @param Node[][] $usedNameNodes
     */
    private function renameNameNode(array $usedNameNodes, string $lastName): void
    {
        /** @var Identifier|Name $usedName */
        // @todo value objects
        foreach ($usedNameNodes as [$usedName, $parentNode]) {
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

    private function resolveUsedNames(Node $searchNode): void
    {
        /** @var Name[] $namedNodes */
        $namedNodes = $this->betterNodeFinder->findInstanceOf($searchNode, Name::class);

        foreach ($namedNodes as $nameNode) {
            /** node name before becoming FQN - attribute from @see NameResolver */
            $originalName = $nameNode->getAttribute('originalName');
            if (! $originalName instanceof Name) {
                continue;
            }

            $parentNode = $nameNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode === null) {
                throw new ShouldNotHappenException();
            }

            $this->resolvedNodeNames[$originalName->toString()][] = [$nameNode, $parentNode];
        }
    }

    private function resolveUsedClassNames(Node $searchNode): void
    {
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes([$searchNode]);

        foreach ($classLikes as $classLikeNode) {
            $name = $this->getName($classLikeNode->name);
            $this->resolvedNodeNames[$name][] = [$classLikeNode->name, $classLikeNode];
        }
    }

    private function resolveTraitUseNames(Node $searchNode): void
    {
        /** @var Identifier[] $identifierNodes */
        $identifierNodes = $this->betterNodeFinder->findInstanceOf($searchNode, Identifier::class);

        foreach ($identifierNodes as $identifierNode) {
            $parentNode = $identifierNode->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentNode instanceof UseUse) {
                continue;
            }

            $this->resolvedNodeNames[$identifierNode->name][] = [$identifierNode, $parentNode];
        }
    }

    /**
     * @return string[]
     */
    private function resolveDocPossibleAliases(Node $searchNode): array
    {
        $possibleDocAliases = [];

        $this->traverseNodesWithCallable($searchNode, function (Node $node) use (&$possibleDocAliases): void {
            if ($node->getDocComment() === null) {
                return;
            }

            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

            if ($phpDocInfo->getVarType()) {
                $possibleDocAliases = $this->appendPossibleAliases($phpDocInfo->getVarType(), $possibleDocAliases);
                $possibleDocAliases = $this->appendPossibleAliases($phpDocInfo->getReturnType(), $possibleDocAliases);

                foreach ($phpDocInfo->getParamTypes() as $paramType) {
                    $possibleDocAliases = $this->appendPossibleAliases($paramType, $possibleDocAliases);
                }
            }

            // e.g. "use Dotrine\ORM\Mapping as ORM" etc.
            $matches = Strings::matchAll($node->getDocComment()->getText(), '#\@(?<possible_alias>\w+)(\\\\)?#s');
            foreach ($matches as $match) {
                $possibleDocAliases[] = $match['possible_alias'];
            }
        });

        return array_unique($possibleDocAliases);
    }

    /**
     * @return string[]
     */
    private function appendPossibleAliases(Type $varType, array $possibleDocAliases): array
    {
        if ($varType instanceof AliasedObjectType) {
            $possibleDocAliases[] = $varType->getClassName();
        }
        if ($varType instanceof UnionType) {
            foreach ($varType->getTypes() as $type) {
                $possibleDocAliases = $this->appendPossibleAliases($type, $possibleDocAliases);
            }
        }
        return $possibleDocAliases;
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
}
