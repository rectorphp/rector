<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\Enum_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Enum_;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\DowngradePhp80\NodeAnalyzer\EnumAnalyzer;
use RectorPrefix20220606\Rector\Php81\NodeFactory\ClassFromEnumFactory;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\DowngradeEnumToConstantListClassRectorTest
 */
final class DowngradeEnumToConstantListClassRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\ClassFromEnumFactory
     */
    private $classFromEnumFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\DowngradePhp80\NodeAnalyzer\EnumAnalyzer
     */
    private $enumAnalyzer;
    public function __construct(ClassFromEnumFactory $classFromEnumFactory, ReflectionProvider $reflectionProvider, EnumAnalyzer $enumAnalyzer)
    {
        $this->classFromEnumFactory = $classFromEnumFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->enumAnalyzer = $enumAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade enum to constant list class', [new CodeSample(<<<'CODE_SAMPLE'
enum Direction
{
    case LEFT;

    case RIGHT;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Direction
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Enum_::class, ClassMethod::class];
    }
    /**
     * @param Enum_|ClassMethod $node
     * @return \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Enum_) {
            return $this->classFromEnumFactory->createFromEnum($node);
        }
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->params as $param) {
            if (!$param->type instanceof Name) {
                continue;
            }
            // is enum type?
            $typeName = $this->getName($param->type);
            if (!$this->reflectionProvider->hasClass($typeName)) {
                continue;
            }
            $classLikeReflection = $this->reflectionProvider->getClass($typeName);
            if (!$classLikeReflection->isEnum()) {
                continue;
            }
            $param->type = $this->resolveParamType($classLikeReflection);
            $hasChanged = \true;
            $this->decorateParamDocType($classLikeReflection, $param, $phpDocInfo);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function resolveParamType(ClassReflection $classReflection) : ?Identifier
    {
        return $this->enumAnalyzer->resolveType($classReflection);
    }
    private function decorateParamDocType(ClassReflection $classReflection, Param $param, PhpDocInfo $phpDocInfo) : void
    {
        $constFetchNode = new ConstFetchNode('\\' . $classReflection->getName(), '*');
        $constTypeNode = new ConstTypeNode($constFetchNode);
        $paramName = '$' . $this->getName($param);
        $paramTagValueNode = new ParamTagValueNode($constTypeNode, \false, $paramName, '');
        $phpDocInfo->addTagValueNode($paramTagValueNode);
    }
}
