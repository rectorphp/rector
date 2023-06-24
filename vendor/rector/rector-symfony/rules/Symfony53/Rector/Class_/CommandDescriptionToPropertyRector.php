<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony53\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-5-3-lazy-command-description
 *
 * @see \Rector\Symfony\Tests\Symfony53\Rector\Class_\CommandDescriptionToPropertyRector\CommandDescriptionToPropertyRectorTest
 */
final class CommandDescriptionToPropertyRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Symfony Command description setters are moved to properties', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

final class SunshineCommand extends Command
{
    public function configure()
    {
        $this->setDescription('sunshine description');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

final class SunshineCommand extends Command
{
    protected static $defaultDescription = 'sunshine description';

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
        // already set → skip
        $defaultNameProperty = $node->getProperty('defaultDescription');
        if ($defaultNameProperty instanceof Property) {
            return null;
        }
        $commandDescriptionString = $this->resolveCommandDescriptionFromSetDescription($node);
        if (!$commandDescriptionString instanceof String_) {
            return null;
        }
        $defaultDescriptionProperty = $this->createStaticProtectedPropertyWithDefault($commandDescriptionString);
        return $this->addDefaultDescriptionProperty($node, $defaultDescriptionProperty);
    }
    private function resolveCommandDescriptionFromSetDescription(Class_ $class) : ?String_
    {
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod || $classMethod->stmts === null) {
            return null;
        }
        foreach ($classMethod->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $stmt->expr;
            if ($methodCall->var instanceof Variable) {
                return $this->resolveFromNonFluentMethodCall($methodCall, $classMethod, $key);
            }
            $string = null;
            $this->traverseNodesWithCallable($stmt, function (Node $node) use(&$string) {
                if ($node instanceof MethodCall && $this->isName($node->name, 'setDescription')) {
                    $string = $this->matchFirstArgString($node);
                    // remove nested call
                    return $node->var;
                }
                return null;
            });
            return $string;
        }
        return null;
    }
    private function createStaticProtectedPropertyWithDefault(String_ $string) : Property
    {
        $property = new \PhpParser\Builder\Property('defaultDescription');
        $property->makeProtected();
        $property->makeStatic();
        $property->setDefault($string);
        return $property->getNode();
    }
    private function addDefaultDescriptionProperty(Class_ $class, Property $defaultDescriptionProperty) : Node
    {
        // When we have property defaultName insert defaultDescription after it.
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if ($this->isName($stmt, 'defaultName')) {
                \array_splice($class->stmts, ++$key, 0, [$defaultDescriptionProperty]);
                return $class;
            }
        }
        $class->stmts = \array_merge([$defaultDescriptionProperty], $class->stmts);
        return $class;
    }
    private function matchFirstArgString(MethodCall $methodCall) : ?\PhpParser\Node\Scalar\String_
    {
        $arg = $methodCall->getArgs()[0] ?? null;
        if (!$arg instanceof Arg) {
            return null;
        }
        if (!$arg->value instanceof String_) {
            return null;
        }
        return $arg->value;
    }
    private function resolveFromNonFluentMethodCall(MethodCall $methodCall, ClassMethod $classMethod, int $key) : ?String_
    {
        if (!$this->isName($methodCall->name, 'setDescription')) {
            return null;
        }
        $string = $this->matchFirstArgString($methodCall);
        if (!$string instanceof String_) {
            return null;
        }
        // cleanup fluent call
        unset($classMethod->stmts[$key]);
        return $string;
    }
}
