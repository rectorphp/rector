<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Enum\ObjectReference;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
/**
 * @see \Rector\Tests\DeadCode\Rector\Class_\RemoveParentDelegatingClassMethodRector\RemoveParentDelegatingClassMethodRectorTest
 */
final class RemoveParentDelegatingClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove class method that only delegates call to parent class method with the same values', [new CodeSample(<<<'CODE_SAMPLE'
class SomeCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeCommand extends Command
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $className = $this->getName($node);
        if (!is_string($className)) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->isClass()) {
            return null;
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->isDelegatingToParent($stmt, $parentClassReflection)) {
                continue;
            }
            unset($node->stmts[$key]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $node->stmts = array_values($node->stmts);
        return $node;
    }
    private function isDelegatingToParent(ClassMethod $classMethod, ClassReflection $parentClassReflection): bool
    {
        // skip constructors, as removing a delegating constructor is risky and usually intentional
        if ($this->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        if ($classMethod->isFinal() || $classMethod->isAbstract() || $classMethod->isPrivate() || $classMethod->byRef) {
            return \false;
        }
        if ($classMethod->stmts === null || count($classMethod->stmts) !== 1) {
            return \false;
        }
        if ($classMethod->attrGroups !== []) {
            return \false;
        }
        if (!$this->isParentCallDelegatingParams($classMethod, $classMethod->stmts[0])) {
            return \false;
        }
        if ($this->hasRefiningDocblock($classMethod)) {
            return \false;
        }
        $parentMethodReflection = $this->matchParentMethodReflection($classMethod, $parentClassReflection);
        if (!$parentMethodReflection instanceof ExtendedMethodReflection) {
            return \false;
        }
        return $this->isSignatureMatching($classMethod, $parentMethodReflection);
    }
    private function hasRefiningDocblock(ClassMethod $classMethod): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $found = \false;
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if ($phpDocChildNode instanceof PhpDocTagNode) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function matchParentMethodReflection(ClassMethod $classMethod, ClassReflection $parentClassReflection): ?ExtendedMethodReflection
    {
        $methodName = $classMethod->name->toString();
        if (!$parentClassReflection->hasNativeMethod($methodName)) {
            return null;
        }
        $extendedMethodReflection = $parentClassReflection->getNativeMethod($methodName);
        // a private parent method is not visible in the child scope
        if ($extendedMethodReflection->isPrivate()) {
            return null;
        }
        return $extendedMethodReflection;
    }
    /**
     * Body must be a sole parent::sameMethod($sameParams, ...) call, passing every param in the same order
     */
    private function isParentCallDelegatingParams(ClassMethod $classMethod, Stmt $soleStmt): bool
    {
        $staticCall = $this->matchParentStaticCall($soleStmt, $classMethod->name->toString());
        if (!$staticCall instanceof StaticCall) {
            return \false;
        }
        $args = $staticCall->getArgs();
        $params = $classMethod->getParams();
        if (count($args) !== count($params)) {
            return \false;
        }
        $paramNames = [];
        foreach ($params as $param) {
            if ($param->variadic || $param->byRef || $param->default instanceof Node || $param->attrGroups !== []) {
                return \false;
            }
            $paramNames[] = $this->getName($param->var);
        }
        $argNames = [];
        foreach ($args as $arg) {
            if ($arg->name instanceof Node || $arg->unpack) {
                return \false;
            }
            if (!$arg->value instanceof Variable) {
                return \false;
            }
            $argNames[] = $this->getName($arg->value);
        }
        return $paramNames === $argNames;
    }
    private function matchParentStaticCall(Stmt $stmt, string $methodName): ?StaticCall
    {
        if ($stmt instanceof Return_) {
            $expr = $stmt->expr;
        } elseif ($stmt instanceof Expression) {
            $expr = $stmt->expr;
        } else {
            return null;
        }
        if (!$expr instanceof StaticCall) {
            return null;
        }
        if ($expr->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($expr->class, ObjectReference::PARENT)) {
            return null;
        }
        if (!$this->isName($expr->name, $methodName)) {
            return null;
        }
        return $expr;
    }
    private function isSignatureMatching(ClassMethod $classMethod, ExtendedMethodReflection $extendedMethodReflection): bool
    {
        if ($classMethod->isStatic() !== $extendedMethodReflection->isStatic()) {
            return \false;
        }
        if ($classMethod->isPublic() !== $extendedMethodReflection->isPublic()) {
            return \false;
        }
        $extendedParametersAcceptor = $extendedMethodReflection->getOnlyVariant();
        $parameterReflections = $extendedParametersAcceptor->getParameters();
        foreach ($classMethod->getParams() as $position => $param) {
            $parameterReflection = $parameterReflections[$position] ?? null;
            if (!$parameterReflection instanceof ExtendedParameterReflection) {
                return \false;
            }
            // no type override
            if ($param->type === null) {
                continue;
            }
            // compare native types only, as the child method may add a native type
            // the parent only has in a docblock - removing it would drop a runtime check
            $parentParameterType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getNativeType(), TypeKind::PARAM);
            if (!$this->nodeComparator->areNodesEqual($param->type, $parentParameterType)) {
                return \false;
            }
        }
        // no return type override
        if (!$classMethod->returnType instanceof Node) {
            return \true;
        }
        $parentReturnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($extendedParametersAcceptor->getNativeReturnType(), TypeKind::RETURN);
        return $this->nodeComparator->areNodesEqual($classMethod->returnType, $parentReturnType);
    }
}
