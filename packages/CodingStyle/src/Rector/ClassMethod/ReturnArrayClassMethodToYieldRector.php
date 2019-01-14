<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
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
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @param string[][] $methodsByType
     */
    public function __construct(
        array $methodsByType,
        NodeTransformer $nodeTransformer,
        DocBlockAnalyzer $docBlockAnalyzer
    ) {
        $this->methodsByType = $methodsByType;
        $this->nodeTransformer = $nodeTransformer;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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

                $yieldNodes = $this->nodeTransformer->transformArrayToYields($arrayNode);
                // remove whole return node
                $this->removeNode($arrayNode->getAttribute(Attribute::PARENT_NODE));

                // remove doc block
                $this->docBlockAnalyzer->removeTagFromNode($node, 'return');

                // change return typehint
                $node->returnType = new FullyQualified(Iterator::class);

                $node->stmts = array_merge((array) $node->stmts, $yieldNodes);
            }
        }

        return $node;
    }

    private function collectReturnArrayNodesFromClassMethod(ClassMethod $classMethodNode): ?Array_
    {
        if ($classMethodNode->stmts === null) {
            return null;
        }

        foreach ($classMethodNode->stmts as $statement) {
            if ($statement instanceof Return_) {
                if ($statement->expr instanceof Array_) {
                    return $statement->expr;
                }
            }
        }

        return null;
    }
}
