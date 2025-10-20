<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\Guard\ParamTypeAddGuard;
use Rector\TypeDeclarationDocblocks\NodeFinder\ArrayDimFetchFinder;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamFromDimFetchKeyUseRector\AddParamFromDimFetchKeyUseRectorTest
 */
final class AddParamFromDimFetchKeyUseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArrayDimFetchFinder $arrayDimFetchFinder;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     */
    private ParamTypeAddGuard $paramTypeAddGuard;
    public function __construct(ArrayDimFetchFinder $arrayDimFetchFinder, StaticTypeMapper $staticTypeMapper, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, ParamTypeAddGuard $paramTypeAddGuard)
    {
        $this->arrayDimFetchFinder = $arrayDimFetchFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->paramTypeAddGuard = $paramTypeAddGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add method param type based on use in array dim fetch of known keys', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function get($key)
    {
        $data = [
            'name' => 'John',
            'age' => 30,
        ];

        return $data[$key];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function get(string $key)
    {
        $data = [
            'name' => 'John',
            'age' => 30,
        ];

        return $data[$key];
    }
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
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->params === []) {
                continue;
            }
            if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod)) {
                continue;
            }
            foreach ($classMethod->getParams() as $param) {
                if ($param->type instanceof Node) {
                    continue;
                }
                $paramName = $this->getName($param);
                $dimFetches = $this->arrayDimFetchFinder->findByDimName($classMethod, $paramName);
                if ($dimFetches === []) {
                    continue;
                }
                if (!$this->paramTypeAddGuard->isLegal($param, $classMethod)) {
                    continue;
                }
                foreach ($dimFetches as $dimFetch) {
                    $dimFetchType = $this->getType($dimFetch->var);
                    if (!$dimFetchType instanceof ArrayType && !$dimFetchType instanceof ConstantArrayType) {
                        continue 2;
                    }
                    if ($dimFetch->dim instanceof Variable) {
                        $type = $this->nodeTypeResolver->getType($dimFetch->dim);
                        if ($type instanceof UnionType) {
                            continue 2;
                        }
                    }
                }
                $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($dimFetchType->getKeyType(), TypeKind::PARAM);
                if (!$paramTypeNode instanceof Node) {
                    continue;
                }
                $param->type = $paramTypeNode;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
