<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * As of PHP 8.1.0, calling `Reflection*::setAccessible()` has no effect.
 *
 * @see https://www.php.net/manual/en/reflectionmethod.setaccessible.php
 * @see https://www.php.net/manual/en/reflectionproperty.setaccessible.php
 * @see \Rector\Tests\Php81\Rector\MethodCall\RemoveReflectionSetAccessibleCallsRector\RemoveReflectionSetAccessibleCallsRectorTest
 */
final class RemoveReflectionSetAccessibleCallsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?int
    {
        if ($node->expr instanceof MethodCall === \false) {
            return null;
        }
        $methodCall = $node->expr;
        if ($this->isName($methodCall->name, 'setAccessible') === \false) {
            return null;
        }
        if ($this->isObjectType($methodCall->var, new ObjectType('ReflectionProperty')) || $this->isObjectType($methodCall->var, new ObjectType('ReflectionMethod'))) {
            return NodeVisitor::REMOVE_NODE;
        }
        return null;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove Reflection::setAccessible() calls', [new CodeSample(<<<'CODE_SAMPLE'
$reflectionProperty = new ReflectionProperty($object, 'property');
$reflectionProperty->setAccessible(true);
$value = $reflectionProperty->getValue($object);

$reflectionMethod = new ReflectionMethod($object, 'method');
$reflectionMethod->setAccessible(false);
$reflectionMethod->invoke($object);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$reflectionProperty = new ReflectionProperty($object, 'property');
$value = $reflectionProperty->getValue($object);

$reflectionMethod = new ReflectionMethod($object, 'method');
$reflectionMethod->invoke($object);
CODE_SAMPLE
)]);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_81;
    }
}
