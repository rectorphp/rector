<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\MakeCommandLazyRector\MakeCommandLazyRectorTest
 */
final class MakeCommandLazyRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make Symfony commands lazy', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

final class SunshineCommand extends Command
{
    public function configure()
    {
        $this->setName('sunshine');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

final class SunshineCommand extends Command
{
    protected static $defaultName = 'sunshine';
    public function configure()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return null;
        }
        $defaultNameProperty = $node->getProperty('defaultName');
        if ($defaultNameProperty instanceof Property) {
            return null;
        }
        $commandNameExpr = $this->resolveCommandNameFromSetName($node);
        if (!$commandNameExpr instanceof Expr) {
            return null;
        }
        $commandNameType = $this->getType($commandNameExpr);
        if (!$commandNameType->isConstantScalarValue()->yes()) {
            return null;
        }
        $defaultNameProperty = $this->createStaticProtectedPropertyWithDefault('defaultName', $commandNameExpr);
        $node->stmts = \array_merge([$defaultNameProperty], $node->stmts);
        return $node;
    }
    private function resolveCommandNameFromSetName(Class_ $class) : ?Expr
    {
        $configureClassMethod = $class->getMethod('configure');
        if (!$configureClassMethod instanceof ClassMethod || $configureClassMethod->stmts === null) {
            return null;
        }
        foreach ($configureClassMethod->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $stmt->expr;
            if ($methodCall->var instanceof Variable) {
                return $this->resolveFromNonFluentMethodCall($methodCall, $configureClassMethod, $key);
            }
            $expr = null;
            $this->traverseNodesWithCallable($stmt, function (Node $node) use(&$expr) {
                if ($node instanceof MethodCall && $this->isName($node->name, 'setName')) {
                    $expr = $node->getArgs()[0]->value;
                    // remove nested call
                    return $node->var;
                }
                return null;
            });
            return $expr;
        }
        return null;
    }
    private function resolveFromNonFluentMethodCall(MethodCall $methodCall, ClassMethod $classMethod, int $key) : ?Expr
    {
        if (!$this->isName($methodCall->name, 'setName')) {
            return null;
        }
        $expr = $methodCall->getArgs()[0]->value;
        // cleanup fluent call
        unset($classMethod->stmts[$key]);
        return $expr;
    }
    private function createStaticProtectedPropertyWithDefault(string $name, Node $node) : Property
    {
        $property = new \PhpParser\Builder\Property($name);
        $property->makeProtected();
        $property->makeStatic();
        $property->setDefault($node);
        return $property->getNode();
    }
}
