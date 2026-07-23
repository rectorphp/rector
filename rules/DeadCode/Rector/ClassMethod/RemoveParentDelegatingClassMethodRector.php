<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Enum\ObjectReference;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveParentDelegatingClassMethodRector\RemoveParentDelegatingClassMethodRectorTest
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
    public function __construct(StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?int
    {
        // constructors are handled by RemoveParentDelegatingConstructorRector
        if ($this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        if ($node->isFinal() || $node->isAbstract() || $node->isPrivate() || $node->byRef) {
            return null;
        }
        if ($node->stmts === null || count($node->stmts) !== 1) {
            return null;
        }
        if ($node->attrGroups !== []) {
            return null;
        }
        if ($this->hasRefiningDocblock($node)) {
            return null;
        }
        $parentMethodReflection = $this->matchParentMethodReflection($node);
        if (!$parentMethodReflection instanceof ExtendedMethodReflection) {
            return null;
        }
        if (!$this->isParentCallDelegatingParams($node, $node->stmts[0])) {
            return null;
        }
        if (!$this->isSignatureMatching($node, $parentMethodReflection)) {
            return null;
        }
        return NodeVisitor::REMOVE_NODE;
    }
    private function hasRefiningDocblock(ClassMethod $classMethod): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByNames(['@param', '@phpstan-param', '@psalm-param', '@return', '@phpstan-return', '@psalm-return', '@deprecated']);
    }
    private function matchParentMethodReflection(ClassMethod $classMethod): ?ExtendedMethodReflection
    {
        $scope = ScopeFetcher::fetch($classMethod);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isClass()) {
            return null;
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            return null;
        }
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
