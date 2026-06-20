<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\AddParamTypeToRefactorMethodRector\AddParamTypeToRefactorMethodRectorTest
 */
final class AddParamTypeToRefactorMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, ReflectionProvider $reflectionProvider, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->reflectionProvider = $reflectionProvider;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param node type to refactor() method based on getNodeTypes() of a Rector rule, to avoid instanceof checks and unknown types reported by PHPStan', [new CodeSample(<<<'CODE_SAMPLE'
public function getNodeTypes(): array
{
    return [MethodCall::class];
}

public function refactor(Node $node): ?Node
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
public function getNodeTypes(): array
{
    return [MethodCall::class];
}

/**
 * @param MethodCall $node
 */
public function refactor(Node $node): ?Node
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->extends instanceof Name) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType('Rector\Rector\AbstractRector'))) {
            return null;
        }
        $refactorClassMethod = $node->getMethod('refactor');
        if (!$refactorClassMethod instanceof ClassMethod) {
            return null;
        }
        $param = $refactorClassMethod->params[0] ?? null;
        if ($param === null) {
            return null;
        }
        $refactorPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($refactorClassMethod);
        // only add when missing, never override an existing, possibly more precise @param
        if ($refactorPhpDocInfo->getParamTagValueByName('node') instanceof ParamTagValueNode) {
            return null;
        }
        $nodeType = $this->matchSingleNodeTypesType($node);
        if (!$nodeType instanceof Type) {
            return null;
        }
        $hasChanged = $this->phpDocTypeChanger->changeParamType($refactorClassMethod, $refactorPhpDocInfo, $nodeType, $param, 'node');
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function matchSingleNodeTypesType(Class_ $class): ?Type
    {
        $getNodeTypesClassMethod = $class->getMethod('getNodeTypes');
        if (!$getNodeTypesClassMethod instanceof ClassMethod) {
            return null;
        }
        $soleReturn = $getNodeTypesClassMethod->stmts[0] ?? null;
        if (!$soleReturn instanceof Return_) {
            return null;
        }
        // only inline array literals, e.g. "return [MethodCall::class];"
        // constant groups like NodeGroup::STMTS_AWARE expand to an unwieldy union
        if (!$soleReturn->expr instanceof Array_) {
            return null;
        }
        $nodeTypes = $this->valueResolver->getValue($soleReturn->expr);
        if (!is_array($nodeTypes) || $nodeTypes === []) {
            return null;
        }
        $objectTypes = [];
        foreach ($nodeTypes as $nodeType) {
            if (!is_string($nodeType)) {
                return null;
            }
            // skip interface node types, they expand to an unwieldy union of all implementers
            if (!$this->reflectionProvider->hasClass($nodeType)) {
                return null;
            }
            if ($this->reflectionProvider->getClass($nodeType)->isInterface()) {
                return null;
            }
            $objectTypes[] = new ObjectType($nodeType);
        }
        return TypeCombinator::union(...$objectTypes);
    }
}
