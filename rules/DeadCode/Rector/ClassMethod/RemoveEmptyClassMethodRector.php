<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Configuration\Parameter\FeatureFlags;
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\DeadCode\NodeManipulator\ControllerClassMethodManipulator;
use Rector\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeManipulator\ClassMethodManipulator;
use Rector\PHPStan\ScopeFetcher;
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
     */
    private ClassMethodManipulator $classMethodManipulator;
    /**
     * @readonly
     */
    private ControllerClassMethodManipulator $controllerClassMethodManipulator;
    /**
     * @readonly
     */
    private ParamAnalyzer $paramAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer;
    public function __construct(ClassMethodManipulator $classMethodManipulator, ControllerClassMethodManipulator $controllerClassMethodManipulator, ParamAnalyzer $paramAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->controllerClassMethodManipulator = $controllerClassMethodManipulator;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
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
            if ($stmt->isFinal() && !$node->isFinal() && FeatureFlags::treatClassesAsFinal() === \false) {
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
        if ($class->isFinal() || FeatureFlags::treatClassesAsFinal()) {
            return $class->isAbstract();
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
        // is method called somewhere else in the class?
        $scope = ScopeFetcher::fetch($class);
        if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($class, $classMethod, $scope)) {
            return \true;
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
        if ($this->isName($classMethod, MethodName::CONSTRUCT)) {
            // has parent class?
            return $class->extends instanceof FullyQualified;
        }
        return $this->isName($classMethod, MethodName::INVOKE);
    }
    private function hasDeprecatedAnnotation(ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        return $phpDocInfo->hasByType(DeprecatedTagValueNode::class);
    }
}
