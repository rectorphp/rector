<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\Annotations;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory;
use Rector\Symfony\ValueObject\PropertyAndAnnotation;
final class PropertyAnnotationAssertResolver
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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(ValueResolver $valueResolver, DoctrineAnnotationFromNewFactory $doctrineAnnotationFromNewFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->doctrineAnnotationFromNewFactory = $doctrineAnnotationFromNewFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(Stmt $stmt) : ?PropertyAndAnnotation
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $stmt->expr;
        if (!$this->nodeNameResolver->isName($methodCall->name, 'addPropertyConstraint')) {
            return null;
        }
        $args = $methodCall->getArgs();
        $constraintsExpr = $args[1]->value;
        $propertyName = $this->valueResolver->getValue($args[0]->value);
        if (!\is_string($propertyName)) {
            return null;
        }
        if (!$constraintsExpr instanceof New_) {
            // nothing we can do... or can we?
            return null;
        }
        $doctrineAnnotationTagValueNode = $this->doctrineAnnotationFromNewFactory->create($constraintsExpr);
        return new PropertyAndAnnotation($propertyName, $doctrineAnnotationTagValueNode);
    }
}
