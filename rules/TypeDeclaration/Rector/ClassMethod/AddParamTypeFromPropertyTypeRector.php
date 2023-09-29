<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\Guard\ParamTypeAddGuard;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector\AddParamTypeFromPropertyTypeRectorTest
 */
final class AddParamTypeFromPropertyTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\ParamTypeAddGuard
     */
    private $paramTypeAddGuard;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Adds param type declaration based on property type the value is assigned to PHPUnit provider return type declaration';
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, TypeFactory $typeFactory, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, ParamTypeAddGuard $paramTypeAddGuard, StaticTypeMapper $staticTypeMapper)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->paramTypeAddGuard = $paramTypeAddGuard;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private string $name;

    public function setName($name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private string $name;

    public function setName(string $name)
    {
        $this->name = $name;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        $hasChanged = \false;
        foreach ($node->params as $param) {
            // already known type → skip
            if ($param->type instanceof Node) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            if (!$this->paramTypeAddGuard->isLegal($param, $node)) {
                continue;
            }
            $paramName = $this->getName($param);
            $propertyStaticTypes = $this->resolvePropertyStaticTypesByParamName($node, $paramName);
            $possibleParamType = $this->typeFactory->createMixedPassedOrUnionType($propertyStaticTypes);
            $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($possibleParamType, TypeKind::PARAM);
            if (!$paramType instanceof Node) {
                continue;
            }
            if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($node)) {
                return null;
            }
            $param->type = $paramType;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @return Type[]
     */
    private function resolvePropertyStaticTypesByParamName(ClassMethod $classMethod, string $paramName) : array
    {
        $propertyStaticTypes = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use($paramName, &$propertyStaticTypes) : ?int {
            if ($node instanceof Class_ || $node instanceof Function_) {
                // skip anonymous classes and inner function
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$this->propertyFetchAnalyzer->isVariableAssignToThisPropertyFetch($node, $paramName)) {
                return null;
            }
            $exprType = $this->nodeTypeResolver->getNativeType($node->expr);
            $nodeExprType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($exprType, TypeKind::PARAM);
            $varType = $this->nodeTypeResolver->getNativeType($node->var);
            $nodeVarType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY);
            if ($nodeExprType instanceof Node && !$this->nodeComparator->areNodesEqual($nodeExprType, $nodeVarType)) {
                return null;
            }
            $propertyStaticTypes[] = $varType;
            return null;
        });
        return $propertyStaticTypes;
    }
}
