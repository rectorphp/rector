<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PhpParser\NodeFinder\LocalMethodCallFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector\ClassMethodArrayDocblockParamFromLocalCallsRectorTest
 */
final class ClassMethodArrayDocblockParamFromLocalCallsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private CallTypesResolver $callTypesResolver;
    /**
     * @readonly
     */
    private LocalMethodCallFinder $localMethodCallFinder;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, CallTypesResolver $callTypesResolver, LocalMethodCallFinder $localMethodCallFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->callTypesResolver = $callTypesResolver;
        $this->localMethodCallFinder = $localMethodCallFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param array docblock to a class method based on local call types', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        $this->run(['item1', 'item2']);
    }

    private function run(array $items)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        $this->run(['item1', 'item2']);
    }

    /**
     * @param string[] $items
     */
    private function run(array $items)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->getParams() === []) {
                continue;
            }
            // only private methods have a closed set of local callers; public/protected can be called from outside
            // with a wider type we cannot see here
            if (!$classMethod->isPrivate()) {
                continue;
            }
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $methodCalls = $this->localMethodCallFinder->match($node, $classMethod);
            $classMethodParameterTypes = $this->callTypesResolver->resolveTypesFromCalls($methodCalls);
            foreach ($classMethod->getParams() as $parameterPosition => $param) {
                if (!$this->hasParamArrayType($param)) {
                    continue;
                }
                $parameterName = $this->getName($param);
                $parameterTagValueNode = $classMethodPhpDocInfo->getParamTagValueByName($parameterName);
                // already known, skip
                if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($parameterTagValueNode)) {
                    continue;
                }
                $resolvedParameterType = $classMethodParameterTypes[$parameterPosition] ?? $classMethodParameterTypes[$parameterName] ?? null;
                if (!$resolvedParameterType instanceof Type) {
                    continue;
                }
                // in case of array type declaration, null cannot be passed or is already casted
                $resolvedParameterType = TypeCombinator::removeNull($resolvedParameterType);
                // the generalization of a nested array (an array of arrays) is imprecise and can diverge from the type
                // PHPStan itself infers at the call site, producing a @param that rejects the very call it was built from
                if ($this->hasNestedArray($resolvedParameterType)) {
                    continue;
                }
                // the param default value must always be accepted; a locally inferred, flow-narrowed type such as
                // "non-empty-array" would otherwise contradict an "= []" default - unite with the default type so
                // the resulting @param never conflicts with the method signature
                if ($param->default instanceof Expr) {
                    $defaultType = $this->nodeTypeResolver->getType($param->default);
                    $resolvedParameterType = TypeCombinator::union($resolvedParameterType, $defaultType);
                }
                $hasClassMethodChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($resolvedParameterType, $classMethodPhpDocInfo, $classMethod, $param, $parameterName);
                if ($hasClassMethodChanged) {
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function hasNestedArray(Type $type): bool
    {
        if ($type instanceof UnionType) {
            $found = \false;
            foreach ($type->getTypes() as $unionedType) {
                if ($this->hasNestedArray($unionedType)) {
                    $found = \true;
                    break;
                }
            }
            return $found;
        }
        if (!$type instanceof ArrayType) {
            return \false;
        }
        $itemType = $type->getItemType();
        if ($itemType instanceof UnionType) {
            $found = \false;
            foreach ($itemType->getTypes() as $unionedItemType) {
                if ($unionedItemType instanceof ArrayType) {
                    $found = \true;
                    break;
                }
            }
            return $found;
        }
        return $itemType instanceof ArrayType;
    }
    private function hasParamArrayType(Param $param): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        return $this->isName($param->type, 'array');
    }
}
