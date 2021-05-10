<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://laravel.com/docs/5.8/upgrade#container-generators
 *
 * @see \Rector\Laravel\Tests\Rector\New_\MakeTaggedPassedToParameterIterableTypeRector\MakeTaggedPassedToParameterIterableTypeRectorTest
 */
final class MakeTaggedPassedToParameterIterableTypeRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change param type to iterable, if passed one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class AnotherClass
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function create()
    {
        $tagged = $this->app->tagged('some_tagged');
        return new SomeClass($tagged);
    }
}

class SomeClass
{
    public function __construct(array $items)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class AnotherClass
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function create()
    {
        $tagged = $this->app->tagged('some_tagged');
        return new SomeClass($tagged);
    }
}

class SomeClass
{
    public function __construct(iterable $items)
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
        return [\PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $className = $this->getName($node->class);
        if ($className === null) {
            return null;
        }
        $class = $this->nodeRepository->findClass($className);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        foreach ($node->args as $arg) {
            $this->refactorClassWithArgType($class, $arg);
        }
        return null;
    }
    private function refactorClassWithArgType(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Arg $arg) : void
    {
        $argValueType = $this->getStaticType($arg->value);
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $argumentPosition = (int) $arg->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ARGUMENT_POSITION);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        $param = $constructClassMethod->params[$argumentPosition] ?? null;
        if (!$param instanceof \PhpParser\Node\Param) {
            return;
        }
        $argTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argValueType);
        $param->type = $argTypeNode;
    }
}
