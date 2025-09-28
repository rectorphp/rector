<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PhpParser\NodeFinder\LocalMethodCallFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
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
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, CallTypesResolver $callTypesResolver, LocalMethodCallFinder $localMethodCallFinder, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->callTypesResolver = $callTypesResolver;
        $this->localMethodCallFinder = $localMethodCallFinder;
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
                if ($parameterTagValueNode instanceof ParamTagValueNode) {
                    continue;
                }
                $resolvedParameterType = $classMethodParameterTypes[$parameterPosition] ?? null;
                if (!$resolvedParameterType instanceof Type) {
                    continue;
                }
                if ($resolvedParameterType instanceof MixedType) {
                    continue;
                }
                if ($resolvedParameterType instanceof ArrayType && $resolvedParameterType->getItemType() instanceof MixedType && $resolvedParameterType->getKeyType() instanceof MixedType) {
                    continue;
                }
                // in case of array type declaration, null cannot be passed or is already casted
                $resolvedParameterType = TypeCombinator::removeNull($resolvedParameterType);
                $hasClassMethodChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($resolvedParameterType, $classMethodPhpDocInfo, $classMethod, $parameterName);
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
    private function hasParamArrayType(Param $param): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        return $this->isName($param->type, 'array');
    }
}
