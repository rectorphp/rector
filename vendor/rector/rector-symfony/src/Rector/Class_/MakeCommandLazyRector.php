<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use RectorPrefix202208\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\MakeCommandLazyRectorTest
 */
final class MakeCommandLazyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(ParamAnalyzer $paramAnalyzer)
    {
        $this->paramAnalyzer = $paramAnalyzer;
    }
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
        $commandName = $this->resolveCommandName($node);
        if (!$commandName instanceof Node) {
            return null;
        }
        if (!$commandName instanceof String_ && !$commandName instanceof ClassConstFetch) {
            return null;
        }
        $this->removeConstructorIfHasOnlySetNameMethodCall($node);
        $defaultNameProperty = $this->createStaticProtectedPropertyWithDefault('defaultName', $commandName);
        $node->stmts = \array_merge([$defaultNameProperty], $node->stmts);
        return $node;
    }
    private function resolveCommandName(Class_ $class) : ?Node
    {
        $node = $this->resolveCommandNameFromConstructor($class);
        if (!$node instanceof Node) {
            return $this->resolveCommandNameFromSetName($class);
        }
        return $node;
    }
    private function resolveCommandNameFromConstructor(Class_ $class) : ?Node
    {
        $commandName = null;
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use(&$commandName) {
            if (!$node instanceof StaticCall) {
                return null;
            }
            if (!$this->isObjectType($node->class, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            $commandName = $this->matchCommandNameNodeInConstruct($node);
            if (!$commandName instanceof Expr) {
                return null;
            }
            // only valid static property values for name
            if (!$commandName instanceof String_ && !$commandName instanceof ConstFetch) {
                return null;
            }
            // remove if parent name is not string
            \array_shift($node->args);
        });
        return $commandName;
    }
    private function resolveCommandNameFromSetName(Class_ $class) : ?Node
    {
        $commandName = null;
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use(&$commandName) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->isName($node->name, 'setName')) {
                return null;
            }
            $commandName = $node->getArgs()[0]->value;
            $commandNameStaticType = $this->getType($commandName);
            if (!$commandNameStaticType instanceof StringType) {
                return null;
            }
            // is chain call? → remove by variable nulling
            if ($node->var instanceof MethodCall) {
                return $node->var;
            }
            $this->removeNode($node);
        });
        return $commandName;
    }
    private function removeConstructorIfHasOnlySetNameMethodCall(Class_ $class) : void
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return;
        }
        $stmts = (array) $constructClassMethod->stmts;
        if (\count($stmts) !== 1) {
            return;
        }
        $params = $constructClassMethod->getParams();
        if ($this->paramAnalyzer->hasPropertyPromotion($params)) {
            return;
        }
        $onlyNode = $stmts[0];
        if ($onlyNode instanceof Expression) {
            $onlyNode = $onlyNode->expr;
        }
        /** @var Expr|null $onlyNode */
        if ($onlyNode === null) {
            return;
        }
        if (!$onlyNode instanceof StaticCall) {
            return;
        }
        if (!$this->isName($onlyNode->name, MethodName::CONSTRUCT)) {
            return;
        }
        if ($onlyNode->args !== []) {
            return;
        }
        $this->removeNode($constructClassMethod);
    }
    private function matchCommandNameNodeInConstruct(StaticCall $staticCall) : ?Expr
    {
        if (!$this->isName($staticCall->name, MethodName::CONSTRUCT)) {
            return null;
        }
        if (\count($staticCall->args) < 1) {
            return null;
        }
        $firstArg = $staticCall->getArgs()[0];
        $staticType = $this->getType($firstArg->value);
        if (!$staticType instanceof StringType) {
            return null;
        }
        return $firstArg->value;
    }
    private function createStaticProtectedPropertyWithDefault(string $name, Node $node) : Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makeProtected();
        $propertyBuilder->makeStatic();
        $propertyBuilder->setDefault($node);
        return $propertyBuilder->getNode();
    }
}
