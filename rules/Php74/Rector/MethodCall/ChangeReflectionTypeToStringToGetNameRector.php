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
final class ChangeReflectionTypeToStringToGetNameRector extends AbstractRector implements MinPhpVersionInterface
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
        return PhpVersionFeature::REFLECTION_TYPE_GETNAME;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change string calls on ReflectionType', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class, String_::class];
    }
    /**
     * @param MethodCall|String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }
        if ($node->expr instanceof MethodCall) {
            return $this->refactorIfHasReturnTypeWasCalled($node->expr);
        }
        if (!$node->expr instanceof Variable) {
            return null;
        }
        if (!$this->isObjectType($node->expr, new ObjectType('ReflectionType'))) {
            return null;
        }
        $type = $this->nodeTypeResolver->getType($node->expr);
        if (!$type instanceof UnionType) {
            return $this->nodeFactory->createMethodCall($node->expr, self::GET_NAME);
        }
        if (!$this->isWithReflectionType($type)) {
            return $this->nodeFactory->createMethodCall($node->expr, self::GET_NAME);
        }
        return null;
    }
    private function isWithReflectionType(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if (!$type instanceof ObjectType) {
                continue;
            }
            if ($type->getClassName() !== 'ReflectionType') {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?Node
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
    private function refactorIfHasReturnTypeWasCalled(MethodCall $methodCall) : ?Node
    {
        if (!$methodCall->var instanceof Variable) {
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
    private function collectCallByVariable(MethodCall $methodCall) : void
    {
        // bit workaround for now
        if ($methodCall->var instanceof Variable) {
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
    private function shouldSkipMethodCall(MethodCall $methodCall) : bool
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        // just added node → skip it
        if (!$scope instanceof Scope) {
            return \true;
        }
        // is to string retype?
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof String_) {
            return \false;
        }
        // probably already converted
        return !$parentNode instanceof Concat;
    }
    private function isReflectionParameterGetTypeMethodCall(MethodCall $methodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType('ReflectionParameter'))) {
            return \false;
        }
        return $this->isName($methodCall->name, 'getType');
    }
    private function refactorReflectionParameterGetName(MethodCall $methodCall) : Ternary
    {
        $getNameMethodCall = $this->nodeFactory->createMethodCall($methodCall, self::GET_NAME);
        return new Ternary($methodCall, $getNameMethodCall, $this->nodeFactory->createNull());
    }
    private function isReflectionFunctionAbstractGetReturnTypeMethodCall(MethodCall $methodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType('ReflectionFunctionAbstract'))) {
            return \false;
        }
        return $this->isName($methodCall->name, 'getReturnType');
    }
    /**
     * @return \PhpParser\Node|\PhpParser\Node\Expr\Ternary
     */
    private function refactorReflectionFunctionGetReturnType(MethodCall $methodCall)
    {
        $refactoredMethodCall = $this->refactorIfHasReturnTypeWasCalled($methodCall);
        if ($refactoredMethodCall !== null) {
            return $refactoredMethodCall;
        }
        $getNameMethodCall = $this->nodeFactory->createMethodCall($methodCall, self::GET_NAME);
        return new Ternary($methodCall, $getNameMethodCall, $this->nodeFactory->createNull());
    }
}
