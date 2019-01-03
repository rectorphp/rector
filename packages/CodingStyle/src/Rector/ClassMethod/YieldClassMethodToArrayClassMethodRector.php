<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\NodeTransformer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 */
final class YieldClassMethodToArrayClassMethodRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $methodsByType = [];

    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    /**
     * @param string[] $methodsByType
     */
    public function __construct(array $methodsByType, NodeTransformer $nodeTransformer)
    {
        $this->methodsByType = $methodsByType;
        $this->nodeTransformer = $nodeTransformer;
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
        yeild 'event' => 'callback';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeEventSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                ['event' => 'callback']
            ];
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

                $yieldNodes = $this->collectYieldNodesFromClassMethod($node);
                if ($yieldNodes === []) {
                    continue;
                }

                $arrayNode = $this->nodeTransformer->transformYieldsToArray($yieldNodes);
                $this->removeNodes($yieldNodes);

                $returnExpression = new Return_($arrayNode);
                $node->stmts = array_merge($node->stmts, [$returnExpression]);
            }
        }

        return $node;
    }

    /**
     * @return Yield_[]
     */
    private function collectYieldNodesFromClassMethod(ClassMethod $classMethodNode): array
    {
        $yieldNodes = [];

        foreach ($classMethodNode->stmts as $statement) {
            if (! $statement instanceof Expression) {
                continue;
            }

            if ($statement->expr instanceof Yield_) {
                $yieldNodes[] = $statement->expr;
            }
        }

        return $yieldNodes;
    }
}
