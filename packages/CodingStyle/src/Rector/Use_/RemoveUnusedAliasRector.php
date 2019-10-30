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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\Imports\ShortNameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    public function __construct(ClassNaming $classNaming, ShortNameResolver $shortNameResolver)
    {
        $this->classNaming = $classNaming;
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

        $useNamesAliasToName = $this->collectUseNamesAliasToName($node);

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

            // only alias name is used → use last name directly
            if (isset($this->resolvedNodeNames[$aliasName])) {
                // keep to differentiate 2 aliases classes
                if (isset($useNamesAliasToName[$lastName]) && count($useNamesAliasToName[$lastName]) > 1) {
                    continue;
                }

                $this->renameNameNode($this->resolvedNodeNames[$aliasName], $lastName);
                $use->alias = null;
            }
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

    /**
     * @return string[][]
     */
    private function collectUseNamesAliasToName(Use_ $use): array
    {
        $useNamesAliasToName = [];

        $shortNames = $this->shortNameResolver->resolveForNode($use);
        foreach ($shortNames as $alias => $useImport) {
            $shortName = $this->classNaming->getShortName($useImport);
            if ($shortName === $alias) {
                continue;
            }

            $useNamesAliasToName[$shortName][] = $alias;
        }

        return $useNamesAliasToName;
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
        foreach ($usedNameNodes as [$usedName, $parentNode]) {
            if ($parentNode instanceof TraitUse) {
                foreach ($parentNode->traits as $key => $traitName) {
                    if (! $this->areNamesEqual($traitName, $usedName)) {
                        continue;
                    }

                    $parentNode->traits[$key] = new Name($lastName);
                }

                continue;
            }

            if ($parentNode instanceof Class_) {
                if ($parentNode->name !== null) {
                    if ($this->areNamesEqual($parentNode->name, $usedName)) {
                        $parentNode->name = new Identifier($lastName);
                    }
                }

                if ($parentNode->extends !== null) {
                    if ($this->areNamesEqual($parentNode->extends, $usedName)) {
                        $parentNode->extends = new Name($lastName);
                    }
                }

                foreach ($parentNode->implements as $key => $implementNode) {
                    if ($this->areNamesEqual($implementNode, $usedName)) {
                        $parentNode->implements[$key] = new Name($lastName);
                    }
                }

                continue;
            }

            if ($parentNode instanceof Param) {
                if ($parentNode->type !== null) {
                    if ($this->areNamesEqual($parentNode->type, $usedName)) {
                        $parentNode->type = new Name($lastName);
                    }
                }

                continue;
            }

            if ($parentNode instanceof New_) {
                if ($this->areNamesEqual($parentNode->class, $usedName)) {
                    $parentNode->class = new Name($lastName);
                }

                continue;
            }

            if ($parentNode instanceof ClassMethod) {
                if ($parentNode->returnType !== null) {
                    if ($this->areNamesEqual($parentNode->returnType, $usedName)) {
                        $parentNode->returnType = new Name($lastName);
                    }
                }

                continue;
            }

            if ($parentNode instanceof Interface_) {
                foreach ($parentNode->extends as $key => $extendInterfaceName) {
                    if ($this->areNamesEqual($extendInterfaceName, $usedName)) {
                        $parentNode->extends[$key] = new Name($lastName);
                    }
                }

                continue;
            }
        }
    }

    private function resolveSearchNode(Use_ $node): ?Node
    {
        $searchNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($searchNode !== null) {
            return $searchNode;
        }

        return $node->getAttribute(AttributeKey::NEXT_NODE);
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
            $phpDocInfo = $this->getPhpDocInfo($node);
            if ($phpDocInfo->getVarType()) {
                $varType = $phpDocInfo->getVarType();
                if ($varType instanceof AliasedObjectType) {
                    $possibleDocAliases[] = $varType->getClassName();
                }

                $returnType = $phpDocInfo->getReturnType();
                if ($returnType instanceof AliasedObjectType) {
                    $possibleDocAliases[] = $returnType->getClassName();
                }

                foreach ($phpDocInfo->getParamTypes() as $paramType) {
                    if ($paramType instanceof AliasedObjectType) {
                        $possibleDocAliases[] = $paramType->getClassName();
                    }
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
}
