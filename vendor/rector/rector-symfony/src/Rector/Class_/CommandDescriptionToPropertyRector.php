<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-5-3-lazy-command-description
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\CommandDescriptionToPropertyRector\CommandDescriptionToPropertyRectorTest
 */
final class CommandDescriptionToPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Symfony Command description setters are moved to properties', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

final class SunshineCommand extends Command
{
    protected static $defaultName = 'sunshine';
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
    protected static $defaultName = 'sunshine';
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
        $defaultNameProperty = $node->getProperty('defaultDescription');
        if ($defaultNameProperty instanceof Property) {
            return null;
        }
        $commandDescription = $this->resolveCommandDescription($node);
        if (!$commandDescription instanceof Expr) {
            return null;
        }
        if ($this->exprAnalyzer->isDynamicExpr($commandDescription)) {
            return null;
        }
        $defaultDescriptionProperty = $this->createStaticProtectedPropertyWithDefault('defaultDescription', $commandDescription);
        return $this->addDefaultDescriptionProperty($node, $defaultDescriptionProperty);
    }
    private function resolveCommandDescription(Class_ $class) : ?Node
    {
        return $this->resolveCommandDescriptionFromSetDescription($class);
    }
    private function resolveCommandDescriptionFromSetDescription(Class_ $class) : ?Node
    {
        $commandDescription = null;
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$commandDescription) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->isName($node->name, 'setDescription')) {
                return null;
            }
            /** @var Arg $arg */
            $arg = $node->getArgs()[0];
            if (!$this->getType($arg->value) instanceof StringType) {
                return null;
            }
            $commandDescription = $arg->value;
            // is chain call? → remove by variable nulling
            if ($node->var instanceof MethodCall) {
                return $node->var;
            }
            $this->removeNode($node);
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $commandDescription;
    }
    private function createStaticProtectedPropertyWithDefault(string $name, Node $node) : Property
    {
        $property = new \PhpParser\Builder\Property($name);
        $property->makeProtected();
        $property->makeStatic();
        $property->setDefault($node);
        return $property->getNode();
    }
    private function addDefaultDescriptionProperty(Class_ $class, Property $defaultDescriptionProperty) : Node
    {
        // When we have property defaultName insert defaultDescription after it.
        if ($class->getProperty('defaultName') instanceof Property) {
            foreach ($class->stmts as $key => $value) {
                if (!$value instanceof Property) {
                    continue;
                }
                if ($value->props[0]->name->name === 'defaultName') {
                    \array_splice($class->stmts, ++$key, 0, [$defaultDescriptionProperty]);
                    break;
                }
            }
        } else {
            $class->stmts = \array_merge([$defaultDescriptionProperty], $class->stmts);
        }
        return $class;
    }
}
