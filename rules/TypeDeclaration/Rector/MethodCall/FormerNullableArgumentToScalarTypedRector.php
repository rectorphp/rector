<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeTypeAnalyzer\CallTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\FormerNullableArgumentToScalarTypedRectorTest
 */
final class FormerNullableArgumentToScalarTypedRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\CallTypeAnalyzer
     */
    private $callTypeAnalyzer;
    public function __construct(\Rector\TypeDeclaration\NodeTypeAnalyzer\CallTypeAnalyzer $callTypeAnalyzer)
    {
        $this->callTypeAnalyzer = $callTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change null in argument, that is now not nullable anymore', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $this->setValue(null);
    }

    public function setValue(string $value)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $this->setValue('');
    }

    public function setValue(string $value)
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->args === []) {
            return null;
        }
        $methodParameterTypes = $this->callTypeAnalyzer->resolveMethodParameterTypes($node);
        if ($methodParameterTypes === []) {
            return null;
        }
        foreach ($node->args as $key => $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if (!$this->valueResolver->isNull($arg->value)) {
                continue;
            }
            /** @var int $key */
            $this->refactorArg($arg, $methodParameterTypes, $key);
        }
        return $node;
    }
    /**
     * @param Type[] $methodParameterTypes
     */
    private function refactorArg(\PhpParser\Node\Arg $arg, array $methodParameterTypes, int $key) : void
    {
        if (!isset($methodParameterTypes[$key])) {
            return;
        }
        $parameterType = $methodParameterTypes[$key];
        if ($parameterType instanceof \PHPStan\Type\StringType) {
            $arg->value = new \PhpParser\Node\Scalar\String_('');
        }
        if ($parameterType instanceof \PHPStan\Type\IntegerType) {
            $arg->value = new \PhpParser\Node\Scalar\LNumber(0);
        }
        if ($parameterType instanceof \PHPStan\Type\FloatType) {
            $arg->value = new \PhpParser\Node\Scalar\DNumber(0);
        }
        if ($parameterType instanceof \PHPStan\Type\BooleanType) {
            $arg->value = $this->nodeFactory->createFalse();
        }
    }
}
