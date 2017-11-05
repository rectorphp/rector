<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

final class ExceptionAnnotationRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $annotationToMethod = [
        'expectedException' => 'expectException',
        'expectedExceptionMessageRegExp' => 'expectedExceptionMessageRegExp',
        'expectedExceptionCode' => 'expectedExceptionCode',
    ];

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallNodeFactory $methodCallNodeFactory,
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
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

            $methodCallExpressions = [];

            foreach ($tags as $tag) {
                $methodCallExpressions[] = $this->createMethodCallExpressionFromTag($tag, $method);
            }

            $classMethodNode->stmts = array_merge($methodCallExpressions, $classMethodNode->stmts);

            $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, $annotation);
        }

        return $classMethodNode;
    }

    private function createMethodCallExpressionFromTag(Generic $genericTag, string $method): Expression
    {
        $annotationContent = (string)$genericTag->getDescription();
        $annotationContent = ltrim($annotationContent, '\\'); // this is needed due to BuilderHelpers

        $methodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            $method,
            [$annotationContent]
        );

        return new Expression($methodCall);
    }
}
