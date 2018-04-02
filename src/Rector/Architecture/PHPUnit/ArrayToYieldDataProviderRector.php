<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\PHPUnit;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractPHPUnitRector;

final class ArrayToYieldDataProviderRector extends AbstractPHPUnitRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        BuilderFactory $builderFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->builderFactory = $builderFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $node instanceof Return_) {
            return false;
        }

        if (! $this->isInProvideMethod($node)) {
            return false;
        }

        if (! $this->isReturnArrayOfArrays($node)) {
            return false;
        }

        return true;
    }

    /**
     * @param Return_ $returnNode
     */
    public function refactor(Node $returnNode): ?Node
    {
        /** @var Array_ $arrayNode */
        $arrayNode = $returnNode->expr;

        foreach ($arrayNode->items as $arrayItem) {
            $yieldNode = new Yield_($arrayItem->value);

            $this->addNodeAfterNode($yieldNode, $returnNode);
        }

        $this->removeNode = true;

        return $returnNode;
    }

    private function isInProvideMethod(Node $node): bool
    {
        $methodName = $node->getAttribute(Attribute::METHOD_NAME);

        return (bool) Strings::match((string) $methodName, '#^provide*#');
    }

    private function isReturnArrayOfArrays(Return_ $returnNode): bool
    {
        if (! $returnNode->expr instanceof Array_) {
            return false;
        };

        /** @var Array_ $arrayNode */
        $arrayNode = $returnNode->expr;

        foreach ($arrayNode->items as $arrayItem) {
            if (! $arrayItem->value instanceof Array_) {
                return false;
            }
        }

        return true;
    }
}
