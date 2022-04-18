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
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\MakeCommandLazyRectorTest
 */
final class MakeCommandLazyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer)
    {
        $this->paramAnalyzer = $paramAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make Symfony commands lazy', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return null;
        }
        $defaultNameProperty = $node->getProperty('defaultName');
        if ($defaultNameProperty instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        $commandName = $this->resolveCommandName($node);
        if (!$commandName instanceof \PhpParser\Node) {
            return null;
        }
        if (!$commandName instanceof \PhpParser\Node\Scalar\String_ && !$commandName instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return null;
        }
        $this->removeConstructorIfHasOnlySetNameMethodCall($node);
        $defaultNameProperty = $this->createStaticProtectedPropertyWithDefault('defaultName', $commandName);
        $node->stmts = \array_merge([$defaultNameProperty], $node->stmts);
        return $node;
    }
    private function resolveCommandName(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node
    {
        $commandName = $this->resolveCommandNameFromConstructor($class);
        if (!$commandName instanceof \PhpParser\Node) {
            return $this->resolveCommandNameFromSetName($class);
        }
        return $commandName;
    }
    private function resolveCommandNameFromConstructor(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node
    {
        $commandName = null;
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use(&$commandName) {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return null;
            }
            if (!$this->isObjectType($node->class, new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            $commandName = $this->matchCommandNameNodeInConstruct($node);
            if (!$commandName instanceof \PhpParser\Node\Expr) {
                return null;
            }
            // only valid static property values for name
            if (!$commandName instanceof \PhpParser\Node\Scalar\String_ && !$commandName instanceof \PhpParser\Node\Expr\ConstFetch) {
                return null;
            }
            // remove if parent name is not string
            \array_shift($node->args);
        });
        return $commandName;
    }
    private function resolveCommandNameFromSetName(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node
    {
        $commandName = null;
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use(&$commandName) {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->isName($node->name, 'setName')) {
                return null;
            }
            $commandName = $node->getArgs()[0]->value;
            $commandNameStaticType = $this->getType($commandName);
            if (!$commandNameStaticType instanceof \PHPStan\Type\StringType) {
                return null;
            }
            // is chain call? → remove by variable nulling
            if ($node->var instanceof \PhpParser\Node\Expr\MethodCall) {
                return $node->var;
            }
            $this->removeNode($node);
        });
        return $commandName;
    }
    private function removeConstructorIfHasOnlySetNameMethodCall(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
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
        if ($onlyNode instanceof \PhpParser\Node\Stmt\Expression) {
            $onlyNode = $onlyNode->expr;
        }
        /** @var Expr|null $onlyNode */
        if ($onlyNode === null) {
            return;
        }
        if (!$onlyNode instanceof \PhpParser\Node\Expr\StaticCall) {
            return;
        }
        if (!$this->isName($onlyNode->name, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return;
        }
        if ($onlyNode->args !== []) {
            return;
        }
        $this->removeNode($constructClassMethod);
    }
    private function matchCommandNameNodeInConstruct(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr
    {
        if (!$this->isName($staticCall->name, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        if (\count($staticCall->args) < 1) {
            return null;
        }
        $firstArg = $staticCall->getArgs()[0];
        $staticType = $this->getType($firstArg->value);
        if (!$staticType instanceof \PHPStan\Type\StringType) {
            return null;
        }
        return $firstArg->value;
    }
    private function createStaticProtectedPropertyWithDefault(string $name, \PhpParser\Node $node) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($name);
        $propertyBuilder->makeProtected();
        $propertyBuilder->makeStatic();
        $propertyBuilder->setDefault($node);
        return $propertyBuilder->getNode();
    }
}
