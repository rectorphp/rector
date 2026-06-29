<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\NeverType;
use Rector\Enum\ClassName;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\ClosureReturnTypeFromAssertInstanceOfRector\ClosureReturnTypeFromAssertInstanceOfRectorTest
 */
final class ClosureReturnTypeFromAssertInstanceOfRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReturnTypeInferer $returnTypeInferer;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(ReturnTypeInferer $returnTypeInferer, StaticTypeMapper $staticTypeMapper, BetterNodeFinder $betterNodeFinder)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type to closures narrowed by assertInstanceOf() inside PHPUnit test case classes', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $callback = function (object $object) {
            $this->assertInstanceOf(SomeType::class, $object);

            return $object;
        };
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $callback = function (object $object): SomeType {
            $this->assertInstanceOf(SomeType::class, $object);

            return $object;
        };
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
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        // type is already set
        if ($node->returnType instanceof Node) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        if (!$this->isInsideTestCaseClass($scope)) {
            return null;
        }
        // only handle closures narrowed by assertInstanceOf(); plain returns are handled by ClosureReturnTypeRector
        if (!$this->hasAssertInstanceOf($node)) {
            return null;
        }
        $closureReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        // handled by other rules
        if ($closureReturnType instanceof NeverType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($closureReturnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    private function hasAssertInstanceOf(Closure $closure): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($closure->stmts, fn(Node $node): bool => ($node instanceof MethodCall || $node instanceof StaticCall) && $this->isName($node->name, 'assertInstanceOf'));
    }
    private function isInsideTestCaseClass(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // is phpunit test case?
        return $classReflection->is(ClassName::TEST_CASE_CLASS);
    }
}
