<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\AstResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/null-false-standalone-types
 * @changelog https://wiki.php.net/rfc/true-type
 * @see \Rector\Tests\DowngradePhp82\Rector\FunctionLike\DowngradeStandaloneNullTrueFalseReturnTypeRector\DowngradeStandaloneNullTrueFalseReturnTypeRectorTest
 */
final class DowngradeStandaloneNullTrueFalseReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, AstResolver $astResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->astResolver = $astResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FunctionLike::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade standalone return null, true, or false', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): null
    {
        return null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): mixed
    {
        return null;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        $returnType = $node->returnType;
        if (!$returnType instanceof Identifier) {
            return null;
        }
        $returnTypeName = $this->getName($returnType);
        if (!\in_array($returnTypeName, ['null', 'false', 'true'], \true)) {
            return null;
        }
        // in closure and arrow function can't add `@return null` docblock as they are Expr
        // that rely on Stmt
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $this->resolveType($returnType));
        $node->returnType = $this->resolveNativeType($node, $returnType);
        return $node;
    }
    private function resolveType(Identifier $identifier) : Type
    {
        $nodeName = $this->getName($identifier);
        if ($nodeName === 'null') {
            return new NullType();
        }
        if ($nodeName === 'false') {
            return new ConstantBooleanType(\false);
        }
        return new ConstantBooleanType(\true);
    }
    /**
     * @return \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier
     */
    private function resolveNativeType(Node $node, Identifier $identifier)
    {
        if ($node instanceof ClassMethod) {
            $returnTypeFromParent = $this->resolveParentNativeReturnType($node);
            if ($returnTypeFromParent instanceof UnionType || $returnTypeFromParent instanceof NullableType) {
                $this->traverseNodesWithCallable($returnTypeFromParent, static function (Node $subNode) : Node {
                    $subNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
                    return $subNode;
                });
                return $returnTypeFromParent;
            }
        }
        $nodeName = $this->getName($identifier);
        if ($nodeName === 'null') {
            return new Identifier('mixed');
        }
        return new Identifier('bool');
    }
    private function resolveParentNativeReturnType(ClassMethod $classMethod) : ?Node
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }
        $methodName = $classMethod->name->toString();
        $parents = \array_merge(\is_array($classReflection->getParents()) ? $classReflection->getParents() : \iterator_to_array($classReflection->getParents()), \is_array($classReflection->getInterfaces()) ? $classReflection->getInterfaces() : \iterator_to_array($classReflection->getInterfaces()));
        foreach ($parents as $parent) {
            if (!$parent->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($parent->getName(), $methodName);
            if (!$parentClassMethod instanceof ClassMethod) {
                continue;
            }
            return $parentClassMethod->returnType;
        }
        return null;
    }
}
