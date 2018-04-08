<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Before:
 * - @expectedException Exception
 * - @expectedExceptionMessage Message
 *
 * After:
 * - $this->expectException('Exception');
 * - $this->expectExceptionMessage('Message');
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
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(MethodCallNodeFactory $methodCallNodeFactory, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Takes setExpectedException() 2nd and next arguments to own methods in PHPUnit.', [
            new CodeSample(
                '$this->setExpectedException(Exception::class, "Message", "CODE");',
                <<<'CODE_SAMPLE'
                $this->setExpectedException(Exception::class); 
                $this->expectExceptionMessage("Message");
                $this->expectExceptionCode("CODE");
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $node instanceof ClassMethod) {
            return false;
        }

        foreach ($this->annotationToMethod as $annotation => $method) {
            if ($this->docBlockAnalyzer->hasAnnotation($node, $annotation)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        foreach ($this->annotationToMethod as $annotation => $method) {
            if (! $this->docBlockAnalyzer->hasAnnotation($classMethodNode, $annotation)) {
                continue;
            }

            /** @var Generic[] $tags */
            $tags = $this->docBlockAnalyzer->getTagsByName($classMethodNode, $annotation);

            $methodCallExpressions = array_map(function (Generic $tag) use ($method): Expression {
                return $this->createMethodCallExpressionFromTag($tag, $method);
            }, $tags);

            $classMethodNode->stmts = array_merge($methodCallExpressions, $classMethodNode->stmts);

            $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, $annotation);
        }

        return $classMethodNode;
    }

    private function createMethodCallExpressionFromTag(Generic $generic, string $method): Expression
    {
        $annotationContent = (string) $generic->getDescription();
        $annotationContent = ltrim($annotationContent, '\\'); // this is needed due to BuilderHelpers

        $methodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            $method,
            [$annotationContent]
        );

        return new Expression($methodCall);
    }
}
