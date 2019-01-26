<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Use_;

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
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveUnusedAliasRector extends AbstractRector
{
    /**
     * @var Node[][][]
     */
    private $resolvedNodeNames = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes unused use aliases', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Kernel as BaseKernel;

class SomeClass extends BaseKernel
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Kernel;

class SomeClass extends Kernel
{
}
CODE_SAMPLE
            ),
        ]);
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
        if ($this->resolvedNodeNames === []) {
            return null;
        }

        foreach ($node->uses as $use) {
            if ($use->alias === null) {
                continue;
            }

            $lastName = $use->name->getLast();
            $aliasName = $this->getName($use->alias);

            // both are used → nothing to remove
            if (isset($this->resolvedNodeNames[$lastName]) && isset($this->resolvedNodeNames[$aliasName])) {
                continue;
            }

            // only last name is used → no need for alias
            if (isset($this->resolvedNodeNames[$lastName])) {
                $use->alias = null;
                continue;
            }

            // only alias name is used → use last name directly
            if (isset($this->resolvedNodeNames[$aliasName])) {
                $this->renameNameNode($this->resolvedNodeNames[$aliasName], $lastName);
                $use->alias = null;
            }
        }

        return $node;
    }

    private function resolveUsedNameNodes(Use_ $node): void
    {
        $searchNode = $this->resolveSearchNode($node);

        $this->resolvedUsedNames($searchNode);
        $this->resolveUsedClassNames($searchNode);
        $this->resolvedTraitUseNames($searchNode);
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

    private function resolveSearchNode(Use_ $node): Node
    {
        $searchNode = $node->getAttribute(Attribute::PARENT_NODE);
        if ($searchNode) {
            return $searchNode;
        }

        $searchNode = $node->getAttribute(Attribute::NEXT_NODE);
        if ($searchNode) {
            return $searchNode;
        }

        throw new ShouldNotHappenException();
    }

    private function resolvedUsedNames(Node $searchNode): void
    {
        /** @var Name[] $namedNodes */
        $namedNodes = $this->betterNodeFinder->findInstanceOf($searchNode, Name::class);

        foreach ($namedNodes as $nameNode) {
            /** node name before becoming FQN - attribute from @see NameResolver */
            $originalName = $nameNode->getAttribute('originalName');
            if (! $originalName instanceof Name) {
                continue;
            }

            $parentNode = $nameNode->getAttribute(Attribute::PARENT_NODE);
            if ($parentNode === null) {
                throw new ShouldNotHappenException();
            }

            $this->resolvedNodeNames[$originalName->toString()][] = [$nameNode, $parentNode];
        }
    }

    private function resolveUsedClassNames(Node $searchNode): void
    {
        /** @var ClassLike[] $classLikeNodes */
        $classLikeNodes = $this->betterNodeFinder->findInstanceOf($searchNode, ClassLike::class);

        foreach ($classLikeNodes as $classLikeNode) {
            if ($classLikeNode->name === null) { // skip anonymous classes
                continue;
            }

            $name = $this->getName($classLikeNode->name);
            $this->resolvedNodeNames[$name][] = [$classLikeNode->name, $classLikeNode];
        }
    }

    private function resolvedTraitUseNames(Node $searchNode): void
    {
        /** @var Identifier[] $identifierNodes */
        $identifierNodes = $this->betterNodeFinder->findInstanceOf($searchNode, Identifier::class);

        foreach ($identifierNodes as $identifierNode) {
            $parentNode = $identifierNode->getAttribute(Attribute::PARENT_NODE);
            if (! $parentNode instanceof UseUse) {
                continue;
            }

            $this->resolvedNodeNames[$identifierNode->name][] = [$identifierNode, $parentNode];
        }
    }
}
