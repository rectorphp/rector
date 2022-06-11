<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\Annotations;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory;
use Rector\Symfony\ValueObject\ValidatorAssert\ClassMethodAndAnnotation;
final class MethodCallAnnotationAssertResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory
     */
    private $doctrineAnnotationFromNewFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher
     */
    private $stmtMethodCallMatcher;
    public function __construct(ValueResolver $valueResolver, DoctrineAnnotationFromNewFactory $doctrineAnnotationFromNewFactory, \Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher $stmtMethodCallMatcher)
    {
        $this->valueResolver = $valueResolver;
        $this->doctrineAnnotationFromNewFactory = $doctrineAnnotationFromNewFactory;
        $this->stmtMethodCallMatcher = $stmtMethodCallMatcher;
    }
    public function resolve(Stmt $stmt) : ?ClassMethodAndAnnotation
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addGetterConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArgValue = $args[0]->value;
        $propertyName = $this->valueResolver->getValue($firstArgValue);
        $getterMethodName = 'get' . \ucfirst($propertyName);
        $secondArgValue = $args[1]->value;
        if (!$secondArgValue instanceof New_) {
            // nothing we can do... or can we?
            return null;
        }
        $doctrineAnnotationTagValueNode = $this->doctrineAnnotationFromNewFactory->create($secondArgValue);
        return new ClassMethodAndAnnotation($getterMethodName, $doctrineAnnotationTagValueNode);
    }
}
