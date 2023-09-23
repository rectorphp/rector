<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @changelog https://3v4l.org/5PJid
 *
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\YieldDataProviderRector\YieldDataProviderRectorTest
 */
final class YieldDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTransformer
     */
    private $nodeTransformer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder
     */
    private $dataProviderClassMethodFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(NodeTransformer $nodeTransformer, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderClassMethodFinder $dataProviderClassMethodFinder, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeTransformer = $nodeTransformer;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderClassMethodFinder = $dataProviderClassMethodFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns array return to yield in data providers', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest implements TestCase
{
    public static function provideData()
    {
        return [
            ['some text']
        ];
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $dataProviderClassMethods = $this->dataProviderClassMethodFinder->find($node);
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            $array = $this->collectReturnArrayNodesFromClassMethod($dataProviderClassMethod);
            if (!$array instanceof Array_) {
                continue;
            }
            $this->transformArrayToYieldsOnMethodNode($dataProviderClassMethod, $array);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
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
                    return null;
                }
                return $returnedExpr;
            }
        }
        return null;
    }
    private function transformArrayToYieldsOnMethodNode(ClassMethod $classMethod, Array_ $array) : void
    {
        $yields = $this->nodeTransformer->transformArrayToYields($array);
        $this->removeReturnTag($classMethod);
        // change return typehint
        $classMethod->returnType = new FullyQualified('Iterator');
        $commentReturn = [];
        foreach ((array) $classMethod->stmts as $key => $classMethodStmt) {
            if (!$classMethodStmt instanceof Return_) {
                continue;
            }
            $commentReturn = $classMethodStmt->getAttribute(AttributeKey::COMMENTS) ?? [];
            unset($classMethod->stmts[$key]);
        }
        if (isset($yields[0])) {
            $yields[0]->setAttribute(AttributeKey::COMMENTS, \array_merge($commentReturn, $yields[0]->getAttribute(AttributeKey::COMMENTS) ?? []));
        }
        $classMethod->stmts = \array_merge((array) $classMethod->stmts, $yields);
    }
    private function removeReturnTag(ClassMethod $classMethod) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
}
