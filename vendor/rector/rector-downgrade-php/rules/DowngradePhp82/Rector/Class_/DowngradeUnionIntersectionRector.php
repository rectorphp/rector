<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use Rector\NodeManipulator\PropertyDecorator;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202604\Webmozart\Assert\Assert;
/**
 * @changelog https://php.watch/versions/8.2/dnf-types
 *
 * @see \Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeUnionIntersectionRector\DowngradeUnionIntersectionRectorTest
 */
final class DowngradeUnionIntersectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PropertyDecorator $propertyDecorator;
    /**
     * @readonly
     */
    private PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator;
    public function __construct(PropertyDecorator $propertyDecorator, PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->propertyDecorator = $propertyDecorator;
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove the union type with intersection, use docblock based', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public (A&B)|C $foo;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var (A&B)|C
     */
    public $foo;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassLike::class, ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassLike|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof ClassLike) {
            return $this->processFunctionLike($node);
        }
        return $this->processClassLike($node);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     * @return null|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction
     */
    private function processFunctionLike($functionLike)
    {
        $paramDecorated = \false;
        foreach ($functionLike->getParams() as $param) {
            if (!$this->isUnionIntersection($param->type)) {
                continue;
            }
            Assert::isInstanceOf($param->type, UnionType::class);
            $this->phpDocFromTypeDeclarationDecorator->decorateParam($param, $functionLike, [\PHPStan\Type\UnionType::class]);
            $paramDecorated = \true;
        }
        if (!$this->isUnionIntersection($functionLike->returnType)) {
            if ($paramDecorated) {
                return $functionLike;
            }
            return null;
        }
        Assert::isInstanceOf($functionLike->returnType, UnionType::class);
        $this->phpDocFromTypeDeclarationDecorator->decorateReturn($functionLike);
        return $functionLike;
    }
    private function processClassLike(ClassLike $classLike): ?ClassLike
    {
        $hasChanged = \false;
        foreach ($classLike->getProperties() as $property) {
            if (!$this->isUnionIntersection($property->type)) {
                continue;
            }
            Assert::isInstanceOf($property->type, UnionType::class);
            $this->propertyDecorator->decorateWithDocBlock($property, $property->type);
            $property->type = null;
            $hasChanged = \true;
        }
        return $hasChanged ? $classLike : null;
    }
    private function isUnionIntersection(?Node $node): bool
    {
        if (!$node instanceof UnionType) {
            return \false;
        }
        foreach ($node->types as $type) {
            if ($type instanceof IntersectionType) {
                return \true;
            }
        }
        return \false;
    }
}
