<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @changelog https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @changelog https://3v4l.org/5PJid
 *
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\ReturnArrayClassMethodToYieldRectorTest
 */
final class ReturnArrayClassMethodToYieldRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ReturnArrayClassMethodToyield[]
     */
    private $methodsToYields = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTransformer
     */
    private $nodeTransformer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    public function __construct(NodeTransformer $nodeTransformer, CommentsMerger $commentsMerger)
    {
        $this->nodeTransformer = $nodeTransformer;
        $this->commentsMerger = $commentsMerger;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns array return to yield return in specific type and method', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest implements TestCase
{
    public static function provideData()
    {
        return [['some text']];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest implements TestCase
{
    public static function provideData()
    {
        yield ['some text'];
    }
}
CODE_SAMPLE
, [new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', '*provide*')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($this->methodsToYields as $methodToYield) {
            if (!$this->isObjectType($node, $methodToYield->getObjectType())) {
                continue;
            }
            if (!$this->isName($node, $methodToYield->getMethod())) {
                continue;
            }
            $arrayNode = $this->collectReturnArrayNodesFromClassMethod($node);
            if (!$arrayNode instanceof Array_) {
                continue;
            }
            $this->transformArrayToYieldsOnMethodNode($node, $arrayNode);
            $this->commentsMerger->keepParent($node, $arrayNode);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ReturnArrayClassMethodToYield::class);
        $this->methodsToYields = $configuration;
    }
    private function collectReturnArrayNodesFromClassMethod(ClassMethod $classMethod) : ?Array_
    {
        if ($classMethod->stmts === null) {
            return null;
        }
        foreach ($classMethod->stmts as $statement) {
            if ($statement instanceof Return_) {
                $returnedExpr = $statement->expr;
                if (!$returnedExpr instanceof Array_) {
                    continue;
                }
                return $returnedExpr;
            }
        }
        return null;
    }
    private function transformArrayToYieldsOnMethodNode(ClassMethod $classMethod, Array_ $array) : void
    {
        $yieldNodes = $this->nodeTransformer->transformArrayToYields($array);
        // remove whole return node
        $parentNode = $array->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }
        $this->removeReturnTag($classMethod);
        // change return typehint
        $classMethod->returnType = new FullyQualified('Iterator');
        foreach ((array) $classMethod->stmts as $key => $classMethodStmt) {
            if (!$classMethodStmt instanceof Return_) {
                continue;
            }
            unset($classMethod->stmts[$key]);
        }
        $classMethod->stmts = \array_merge((array) $classMethod->stmts, $yieldNodes);
    }
    private function removeReturnTag(ClassMethod $classMethod) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
}
