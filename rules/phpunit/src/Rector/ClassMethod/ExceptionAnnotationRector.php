<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeFactory\ExpectExceptionMethodCallFactory;

/**
 * @see https://thephp.cc/news/2016/02/questioning-phpunit-best-practices
 * @see https://github.com/sebastianbergmann/phpunit/commit/17c09b33ac5d9cad1459ace0ae7b1f942d1e9afd
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\ExceptionAnnotationRectorTest
 */
final class ExceptionAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * In reversed order, which they should be called in code.
     *
     * @var array<string, string>
     */
    private const ANNOTATION_TO_METHOD = [
        'expectedExceptionMessageRegExp' => 'expectExceptionMessageRegExp',
        'expectedExceptionMessage' => 'expectExceptionMessage',
        'expectedExceptionCode' => 'expectExceptionCode',
        'expectedException' => 'expectException',
    ];

    /**
     * @var ExpectExceptionMethodCallFactory
     */
    private $expectExceptionMethodCallFactory;

    public function __construct(ExpectExceptionMethodCallFactory $expectExceptionMethodCallFactory)
    {
        $this->expectExceptionMethodCallFactory = $expectExceptionMethodCallFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes `@expectedException annotations to `expectException*()` methods',
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

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        foreach (self::ANNOTATION_TO_METHOD as $annotationName => $methodName) {
            if (! $phpDocInfo->hasByName($annotationName)) {
                continue;
            }

            $methodCallExpressions = $this->expectExceptionMethodCallFactory->createFromTagValueNodes(
                $phpDocInfo->getTagsByName($annotationName),
                $methodName
            );
            $node->stmts = array_merge($methodCallExpressions, (array) $node->stmts);

            $phpDocInfo->removeByName($annotationName);
        }

        return $node;
    }
}
