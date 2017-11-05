<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\MethodCallNodeFactory;
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

    public function __construct(MethodCallNodeFactory $methodCallNodeFactory, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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

            foreach ($tags as $tag) {
                $annotationContent = (string) $tag->getDescription();
                $methodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
                    'this',
                    $method,
                    [$annotationContent]
                );

                // @todo
//                $this->prependNodeInsideNode($methodCall, $classMethodNode);
            }

            $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, $annotation);
        }

        return $classMethodNode;
    }
}
