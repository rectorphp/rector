<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.reddit.com/r/PHP/comments/apikof/whats_the_deal_with_reflectiontype/ https://www.php.net/manual/en/reflectiontype.tostring.php
 *
 * @changelog https://3v4l.org/fYeif
 * @changelog https://3v4l.org/QeM6U
 *
 * @see \Rector\Tests\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\ChangeReflectionTypeToStringToGetNameRectorTest
 */
final class ChangeReflectionTypeToStringToGetNameRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const GET_NAME = 'getName';
    /**
     * Possibly extract node decorator with scope breakers (Function_, If_) to respect node flow
     * @var string[][]
     */
    private $callsByVariable = [];
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::REFLECTION_TYPE_GETNAME;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change string calls on ReflectionType', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function go(ReflectionFunction $reflectionFunction)
    {
        $parameterReflection = $reflectionFunction->getParameters()[0];

        $paramType = (string) $parameterReflection->getType();

        $stringValue = 'hey' . $reflectionFunction->getReturnType();

        // keep
        return $reflectionFunction->getReturnType();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function go(ReflectionFunction $reflectionFunction)
    {
        $parameterReflection = $reflectionFunction->getParameters()[0];

        $paramType = (string) ($parameterReflection->getType() ? $parameterReflection->getType()->getName() : null);

        $stringValue = 'hey' . ($reflectionFunction->getReturnType() ? $reflectionFunction->getReturnType()->getName() : null);

        // keep
        return $reflectionFunction->getReturnType();
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\Cast\String_::class];
    }
    /**
     * @param MethodCall|String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->refactorMethodCall($node);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->refactorIfHasReturnTypeWasCalled($node->expr);
        }
        if (!$node->expr instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$this->isObjectType($node->expr, new \PHPStan\Type\ObjectType('ReflectionType'))) {
            return null;
        }
        $type = $this->nodeTypeResolver->getType($node->expr);
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return $this->nodeFactory->createMethodCall($node->expr, self::GET_NAME);
        }
        if (!$this->isWithReflectionType($type)) {
            return $this->nodeFactory->createMethodCall($node->expr, self::GET_NAME);
        }
        return null;
    }
    private function isWithReflectionType(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if (!$type instanceof \PHPStan\Type\ObjectType) {
                continue;
            }
            if ($type->getClassName() !== 'ReflectionType') {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $this->collectCallByVariable($methodCall);
        if ($this->shouldSkipMethodCall($methodCall)) {
            return null;
        }
        if ($this->isReflectionParameterGetTypeMethodCall($methodCall)) {
            return $this->refactorReflectionParameterGetName($methodCall);
        }
        if ($this->isReflectionFunctionAbstractGetReturnTypeMethodCall($methodCall)) {
            return $this->refactorReflectionFunctionGetReturnType($methodCall);
        }
        return null;
    }
    private function refactorIfHasReturnTypeWasCalled(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        if (!$methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $variableName = $this->getName($methodCall->var);
        $callsByVariable = $this->callsByVariable[$variableName] ?? [];
        // we already know it has return type
        if (\in_array('hasReturnType', $callsByVariable, \true)) {
            return $this->nodeFactory->createMethodCall($methodCall, self::GET_NAME);
        }
        return null;
    }
    private function collectCallByVariable(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        // bit workaround for now
        if ($methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
            $variableName = $this->getName($methodCall->var);
            $methodName = $this->getName($methodCall->name);
            if (!\is_string($variableName)) {
                return;
            }
            if (!\is_string($methodName)) {
                return;
            }
            $this->callsByVariable[$variableName][] = $methodName;
        }
    }
    private function shouldSkipMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $scope = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // just added node → skip it
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        // is to string retype?
        $parentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\Cast\String_) {
            return \false;
        }
        // probably already converted
        return !$parentNode instanceof \PhpParser\Node\Expr\BinaryOp\Concat;
    }
    private function isReflectionParameterGetTypeMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('ReflectionParameter'))) {
            return \false;
        }
        return $this->isName($methodCall->name, 'getType');
    }
    private function refactorReflectionParameterGetName(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\Ternary
    {
        $getNameMethodCall = $this->nodeFactory->createMethodCall($methodCall, self::GET_NAME);
        return new \PhpParser\Node\Expr\Ternary($methodCall, $getNameMethodCall, $this->nodeFactory->createNull());
    }
    private function isReflectionFunctionAbstractGetReturnTypeMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('ReflectionFunctionAbstract'))) {
            return \false;
        }
        return $this->isName($methodCall->name, 'getReturnType');
    }
    /**
     * @return \PhpParser\Node|\PhpParser\Node\Expr\Ternary
     */
    private function refactorReflectionFunctionGetReturnType(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $refactoredMethodCall = $this->refactorIfHasReturnTypeWasCalled($methodCall);
        if ($refactoredMethodCall !== null) {
            return $refactoredMethodCall;
        }
        $getNameMethodCall = $this->nodeFactory->createMethodCall($methodCall, self::GET_NAME);
        return new \PhpParser\Node\Expr\Ternary($methodCall, $getNameMethodCall, $this->nodeFactory->createNull());
    }
}
