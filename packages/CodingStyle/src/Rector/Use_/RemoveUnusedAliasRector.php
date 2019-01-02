<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveUnusedAliasRector extends AbstractRector
{
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
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        $usedNameNodes = $this->resolveUsedNameNodes($parentNode);
        if ($usedNameNodes === []) {
            return null;
        }

        foreach ($node->uses as $use) {
            if ($use->alias === null) {
                continue;
            }

            $lastName = $use->name->getLast();
            $aliasName = $this->getName($use->alias);

            // both are used → nothing to remove
            if (isset($usedNameNodes[$lastName]) && isset($usedNameNodes[$aliasName])) {
                continue;
            }

            // only last name is used → no need for alias
            if (isset($usedNameNodes[$lastName])) {
                $use->alias = null;
                continue;
            }

            // only alis name is used → use last name directly
            if (isset($usedNameNodes[$aliasName])) {
                $this->renameNameNode($usedNameNodes[$aliasName], $lastName);
                $use->alias = null;
            }
        }

        return $node;
    }

    /**
     * @return Node[][][]
     */
    private function resolveUsedNameNodes(Node $parentNode): array
    {
        $usedNameNodes = [];

        // assumption for namespaced content
        if ($parentNode instanceof Namespace_) {
            /** @var Name[] $namedNodes */
            $namedNodes = $this->betterNodeFinder->find($parentNode, function (Node $node) {
                if ($node instanceof Name) {
                    return true;
                }

                return false;
            });

            foreach ($namedNodes as $nameNode) {
                /** node name before becoming FQN - attribute from @see NameResolver */
                $originalName = $nameNode->getAttribute('originalName');
                if (! $originalName instanceof Name) {
                    continue;
                }

                /** @var Node $parentNode */
                $parentNode = $nameNode->getAttribute(Attribute::PARENT_NODE);

                $usedNameNodes[$originalName->toString()][] = [$nameNode, $parentNode];
            }
        }

        return $usedNameNodes;
    }

    /**
     * @param Node[][] $usedNameNodes
     */
    private function renameNameNode(array $usedNameNodes, string $lastName): void
    {
        foreach ($usedNameNodes as [$usedName, $parentNode]) {
            foreach ($this->getObjectPublicPropertyNames($parentNode) as $parentNodePropertyName) {
                if ($parentNode->{$parentNodePropertyName} !== $usedName) {
                    continue;
                }

                $parentNode->{$parentNodePropertyName} = new Name($lastName);
            }
        }
    }

    /**
     * @param object $node
     * @return string[]
     */
    private function getObjectPublicPropertyNames($node): array
    {
        return array_keys(get_object_vars($node));
    }
}
