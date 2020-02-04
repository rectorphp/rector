<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://thephp.cc/news/2016/02/questioning-phpunit-best-practices
 * @see https://github.com/sebastianbergmann/phpunit/commit/17c09b33ac5d9cad1459ace0ae7b1f942d1e9afd
 *
 * @see \Rector\PHPUnit\Tests\Rector\ExceptionAnnotationRector\ExceptionAnnotationRectorTest
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * @expectedException Exception
 * @expectedExceptionMessage Message
 */
public function test()
{
    // tested code
}
PHP
                    ,
                    <<<'PHP'
public function test()
{
    $this->expectException('Exception');
    $this->expectExceptionMessage('Message');
    // tested code
}
PHP
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

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        foreach ($this->annotationToMethod as $annotation => $method) {
            if (! $phpDocInfo->hasByName($annotation)) {
                continue;
            }

            /** @var GenericTagValueNode[] $tags */
            $tags = $phpDocInfo->getTagsByName($annotation);

            $methodCallExpressions = array_map(function (PhpDocTagNode $phpDocTagNode) use ($method): Expression {
                $methodCall = $this->createMethodCallExpressionFromTag($phpDocTagNode, $method);
                return new Expression($methodCall);
            }, $tags);

            $node->stmts = array_merge($methodCallExpressions, (array) $node->stmts);

            $phpDocInfo->removeByName($annotation);
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
