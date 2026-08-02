<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\ValidatorAssert;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\ValueObject\ValidatorAssert\ClassMethodAndConstraint;
use Rector\Symfony\ValueObject\ValidatorAssert\PropertyAndConstraint;
/**
 * Resolves the constraint "new" from a single loadValidatorMetadata() statement, so it can be turned into an attribute
 */
final class MetadataConstraintResolver
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private \Rector\Symfony\NodeAnalyzer\ValidatorAssert\StmtMethodCallMatcher $stmtMethodCallMatcher;
    public function __construct(ValueResolver $valueResolver, \Rector\Symfony\NodeAnalyzer\ValidatorAssert\StmtMethodCallMatcher $stmtMethodCallMatcher)
    {
        $this->valueResolver = $valueResolver;
        $this->stmtMethodCallMatcher = $stmtMethodCallMatcher;
    }
    public function resolveClassConstraint(Stmt $stmt): ?New_
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }
        $firstArgValue = $args[0]->value;
        if (!$firstArgValue instanceof New_) {
            return null;
        }
        return $firstArgValue;
    }
    public function resolvePropertyConstraint(Stmt $stmt): ?PropertyAndConstraint
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addPropertyConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        if (count($args) < 2) {
            return null;
        }
        $propertyName = $this->valueResolver->getValue($args[0]->value);
        if (!is_string($propertyName)) {
            return null;
        }
        $constraintExpr = $args[1]->value;
        if (!$constraintExpr instanceof New_) {
            return null;
        }
        return new PropertyAndConstraint($propertyName, $constraintExpr);
    }
    public function resolveGetterConstraint(Stmt $stmt): ?ClassMethodAndConstraint
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addGetterConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        if (count($args) < 2) {
            return null;
        }
        $propertyName = $this->valueResolver->getValue($args[0]->value);
        if (!is_string($propertyName)) {
            return null;
        }
        $constraintExpr = $args[1]->value;
        if (!$constraintExpr instanceof New_) {
            return null;
        }
        // @see https://github.com/symfony/symfony/blob/7d4b42cbeef195e0a01272b9c5f464f0afe52542/src/Symfony/Component/Validator/Mapping/GetterMetadata.php#L45-L47
        $possibleMethodNames = ['get' . ucfirst($propertyName), 'is' . ucfirst($propertyName), 'has' . ucfirst($propertyName)];
        return new ClassMethodAndConstraint($possibleMethodNames, $constraintExpr);
    }
}
