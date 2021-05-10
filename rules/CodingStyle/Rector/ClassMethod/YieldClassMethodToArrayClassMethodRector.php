<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @see https://3v4l.org/5PJid
 *
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\YieldClassMethodToArrayClassMethodRectorTest
 */
final class YieldClassMethodToArrayClassMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHODS_BY_TYPE = 'methods_by_type';
    /**
     * @var array<class-string, string[]>
     */
    private $methodsByType = [];
    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;
    /**
     * @param array<class-string, string[]> $methodsByType
     */
    public function __construct(\Rector\Core\PhpParser\NodeTransformer $nodeTransformer, array $methodsByType = [])
    {
        $this->methodsByType = $methodsByType;
        $this->nodeTransformer = $nodeTransformer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns yield return to array return in specific type and method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield 'event' => 'callback';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}
CODE_SAMPLE
, [self::METHODS_BY_TYPE => ['EventSubscriberInterface' => ['getSubscribedEvents']]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->methodsByType as $type => $methods) {
            if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            foreach ($methods as $method) {
                if (!$this->isName($node, $method)) {
                    continue;
                }
                $yieldNodes = $this->collectYieldNodesFromClassMethod($node);
                if ($yieldNodes === []) {
                    continue;
                }
                $arrayNode = $this->nodeTransformer->transformYieldsToArray($yieldNodes);
                $this->removeNodes($yieldNodes);
                $node->returnType = new \PhpParser\Node\Identifier('array');
                $returnExpression = new \PhpParser\Node\Stmt\Return_($arrayNode);
                $node->stmts = \array_merge((array) $node->stmts, [$returnExpression]);
            }
        }
        return $node;
    }
    public function configure(array $configuration) : void
    {
        $this->methodsByType = $configuration[self::METHODS_BY_TYPE] ?? [];
    }
    /**
     * @return Yield_[]
     */
    private function collectYieldNodesFromClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $yieldNodes = [];
        if ($classMethod->stmts === null) {
            return [];
        }
        foreach ($classMethod->stmts as $statement) {
            if (!$statement instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if ($statement->expr instanceof \PhpParser\Node\Expr\Yield_) {
                $yieldNodes[] = $statement->expr;
            }
        }
        return $yieldNodes;
    }
}
