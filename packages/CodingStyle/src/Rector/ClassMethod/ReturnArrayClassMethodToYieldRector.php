<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\NodeTransformer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @see https://3v4l.org/5PJid
 */
final class ReturnArrayClassMethodToYieldRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $methodsByType = [];

    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @param string[][] $methodsByType
     */
    public function __construct(
        array $methodsByType,
        NodeTransformer $nodeTransformer,
        DocBlockManipulator $docBlockManipulator
    ) {
        $this->methodsByType = $methodsByType;
        $this->nodeTransformer = $nodeTransformer;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns yield return to array return in specific type and method', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield 'event' => 'callback';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}
CODE_SAMPLE
                ,
                [
                    'EventSubscriberInterface' => ['getSubscribedEvents'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodsByType as $type => $methods) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($methods as $method) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                $arrayNode = $this->collectReturnArrayNodesFromClassMethod($node);
                if ($arrayNode === null) {
                    continue;
                }

                $this->transformArrayToYieldsOnMethodNode($node, $arrayNode);
            }
        }

        return $node;
    }

    private function collectReturnArrayNodesFromClassMethod(ClassMethod $classMethod): ?Array_
    {
        if ($classMethod->stmts === null) {
            return null;
        }

        foreach ($classMethod->stmts as $statement) {
            if ($statement instanceof Return_) {
                if ($statement->expr instanceof Array_) {
                    return $statement->expr;
                }
            }
        }

        return null;
    }

    private function transformArrayToYieldsOnMethodNode(ClassMethod $classMethod, Array_ $arrayNode): void
    {
        $yieldNodes = $this->nodeTransformer->transformArrayToYields($arrayNode);

        // remove whole return node
        $parentNode = $arrayNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode === null) {
            throw new ShouldNotHappenException();
        }

        $this->removeNode($parentNode);

        // remove doc block
        $this->docBlockManipulator->removeTagFromNode($classMethod, 'return');

        // change return typehint
        $classMethod->returnType = new FullyQualified(Iterator::class);

        $classMethod->stmts = array_merge((array) $classMethod->stmts, $yieldNodes);
    }
}
