<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://thephp.cc/news/2016/02/questioning-phpunit-best-practices
 * @see https://github.com/sebastianbergmann/phpunit/commit/17c09b33ac5d9cad1459ace0ae7b1f942d1e9afd
 */
final class ExceptionAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * In reversed order, which they should be called in code.
     *
     * @var string[]
     */
    private $annotationToMethod = [
        'expectedExceptionMessageRegExp' => 'expectExceptionMessageRegExp',
        'expectedExceptionMessage' => 'expectExceptionMessage',
        'expectedExceptionCode' => 'expectExceptionCode',
        'expectedException' => 'expectException',
    ];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @expectedException Exception
 * @expectedExceptionMessage Message
 */
public function test()
{
    // tested code
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
public function test()
{
    $this->expectException('Exception');
    $this->expectExceptionMessage('Message');
    // tested code
}
CODE_SAMPLE
                ),
            ]
        );
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        foreach ($this->annotationToMethod as $annotation => $method) {
            if (! $this->docBlockManipulator->hasTag($node, $annotation)) {
                continue;
            }

            /** @var Generic[] $tags */
            $tags = $this->docBlockManipulator->getTagsByName($node, $annotation);

            $methodCallExpressions = array_map(function (PhpDocTagNode $phpDocTagNode) use ($method): Expression {
                $methodCall = $this->createMethodCallExpressionFromTag($phpDocTagNode, $method);
                return new Expression($methodCall);
            }, $tags);

            $node->stmts = array_merge($methodCallExpressions, (array) $node->stmts);

            $this->docBlockManipulator->removeTagFromNode($node, $annotation);
        }

        return $node;
    }

    private function createMethodCallExpressionFromTag(PhpDocTagNode $phpDocTagNode, string $method): MethodCall
    {
        $annotationContent = (string) $phpDocTagNode->value;
        $annotationContent = ltrim($annotationContent, '\\'); // this is needed due to BuilderHelpers

        return $this->createMethodCall('this', $method, [$annotationContent]);
    }
}
