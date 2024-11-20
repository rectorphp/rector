<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\Annotations;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Exception\NotImplementedYetException;
use Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory;
final class ClassAnnotationAssertResolver
{
    /**
     * @readonly
     */
    private \Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher $stmtMethodCallMatcher;
    /**
     * @readonly
     */
    private DoctrineAnnotationFromNewFactory $doctrineAnnotationFromNewFactory;
    public function __construct(\Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher $stmtMethodCallMatcher, DoctrineAnnotationFromNewFactory $doctrineAnnotationFromNewFactory)
    {
        $this->stmtMethodCallMatcher = $stmtMethodCallMatcher;
        $this->doctrineAnnotationFromNewFactory = $doctrineAnnotationFromNewFactory;
    }
    public function resolve(Stmt $stmt) : ?DoctrineAnnotationTagValueNode
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArgValue = $args[0]->value;
        if (!$firstArgValue instanceof New_) {
            throw new NotImplementedYetException();
        }
        return $this->doctrineAnnotationFromNewFactory->create($firstArgValue);
    }
}
