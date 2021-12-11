<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\ReturnTypeFromReturnNewRectorTest
 */
final class ReturnTypeFromReturnNewRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->typeFactory = $typeFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add return type to function like with return new', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function action()
    {
        return new Response();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function action(): Response
    {
        return new Response();
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Expr\ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|ArrowFunction $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\ArrowFunction) {
            $returns = [new \PhpParser\Node\Stmt\Return_($node->expr)];
        } else {
            /** @var Return_[] $returns */
            $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($node, \PhpParser\Node\Stmt\Return_::class);
        }
        if ($returns === []) {
            return null;
        }
        $newTypes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof \PhpParser\Node\Expr\New_) {
                return null;
            }
            $new = $return->expr;
            if (!$new->class instanceof \PhpParser\Node\Name) {
                return null;
            }
            $newTypes[] = $this->createObjectTypeFromNew($new);
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionType($newTypes);
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @return \PHPStan\Type\ObjectType|\PHPStan\Type\StaticType
     */
    private function createObjectTypeFromNew(\PhpParser\Node\Expr\New_ $new)
    {
        $className = $this->getName($new->class);
        if ($className === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($className === \Rector\Core\Enum\ObjectReference::STATIC()->getValue() || $className === \Rector\Core\Enum\ObjectReference::SELF()->getValue()) {
            $scope = $new->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            if (!$scope instanceof \PHPStan\Analyser\Scope) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $classReflection = $scope->getClassReflection();
            if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            if ($className === \Rector\Core\Enum\ObjectReference::SELF()->getValue()) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType($classReflection);
            }
            return new \PHPStan\Type\StaticType($classReflection);
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return new \PHPStan\Type\ObjectType($className, null, $classReflection);
    }
}
