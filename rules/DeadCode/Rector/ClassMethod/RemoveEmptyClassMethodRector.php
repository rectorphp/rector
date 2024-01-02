<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\NodeManipulator\ControllerClassMethodManipulator;
use Rector\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeManipulator\ClassMethodManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\RemoveEmptyClassMethodRectorTest
 */
final class RemoveEmptyClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\ControllerClassMethodManipulator
     */
    private $controllerClassMethodManipulator;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(ClassMethodManipulator $classMethodManipulator, ControllerClassMethodManipulator $controllerClassMethodManipulator, ParamAnalyzer $paramAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->controllerClassMethodManipulator = $controllerClassMethodManipulator;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove empty class methods not required by parents', [new CodeSample(<<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class OrphanClass
{
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
    public function refactor(Node $node) : ?Class_
    {
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }
            if ($stmt->stmts !== null && $stmt->stmts !== []) {
                continue;
            }
            if ($stmt->isAbstract()) {
                continue;
            }
            if ($stmt->isFinal() && !$node->isFinal()) {
                continue;
            }
            if ($this->shouldSkipNonFinalNonPrivateClassMethod($node, $stmt)) {
                continue;
            }
            if ($this->shouldSkipClassMethod($node, $stmt)) {
                continue;
            }
            unset($node->stmts[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipNonFinalNonPrivateClassMethod(Class_ $class, ClassMethod $classMethod) : bool
    {
        if ($class->isFinal()) {
            return \false;
        }
        if ($classMethod->isMagic()) {
            return \false;
        }
        if ($classMethod->isProtected()) {
            return \true;
        }
        return $classMethod->isPublic();
    }
    private function shouldSkipClassMethod(Class_ $class, ClassMethod $classMethod) : bool
    {
        $desiredClassMethodName = $this->getName($classMethod);
        // is method called somewhere else in the class?
        foreach ($class->getMethods() as $anotherClassMethod) {
            if ($anotherClassMethod === $classMethod) {
                continue;
            }
            if ($this->containsMethodCall($anotherClassMethod, $desiredClassMethodName)) {
                return \true;
            }
        }
        if ($this->classMethodManipulator->isNamedConstructor($classMethod)) {
            return \true;
        }
        if ($this->classMethodManipulator->hasParentMethodOrInterfaceMethod($class, $classMethod->name->toString())) {
            return \true;
        }
        if ($this->paramAnalyzer->hasPropertyPromotion($classMethod->params)) {
            return \true;
        }
        if ($this->hasDeprecatedAnnotation($classMethod)) {
            return \true;
        }
        if ($this->controllerClassMethodManipulator->isControllerClassMethod($class, $classMethod)) {
            return \true;
        }
        if ($this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            // has parent class?
            return $class->extends instanceof FullyQualified;
        }
        return $this->nodeNameResolver->isName($classMethod, MethodName::INVOKE);
    }
    private function hasDeprecatedAnnotation(ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        return $phpDocInfo->hasByType(DeprecatedTagValueNode::class);
    }
    private function containsMethodCall(ClassMethod $anotherClassMethod, string $desiredClassMethodName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($anotherClassMethod, function (Node $node) use($desiredClassMethodName) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            if (!$node->var instanceof Variable) {
                return \false;
            }
            if (!$this->isName($node->var, 'this')) {
                return \false;
            }
            return $this->isName($node->name, $desiredClassMethodName);
        });
    }
}
