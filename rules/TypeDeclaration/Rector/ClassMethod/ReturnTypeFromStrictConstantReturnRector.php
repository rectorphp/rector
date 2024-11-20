<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector\ReturnTypeFromStrictConstantReturnRectorTest
 */
final class ReturnTypeFromStrictConstantReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, StaticTypeMapper $staticTypeMapper, BetterNodeFinder $betterNodeFinder, TypeFactory $typeFactory, ReturnAnalyzer $returnAnalyzer)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->typeFactory = $typeFactory;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict type declaration based on returned constants', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public const NAME = 'name';

    public function run()
    {
        return self::NAME;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public const NAME = 'name';

    public function run(): string
    {
        return self::NAME;
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
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($node->returnType instanceof Node) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $matchedType = $this->matchAlwaysReturnConstFetch($returns);
        if (!$matchedType instanceof Type) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($matchedType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    /**
     * @return PhpVersion::*
     */
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param Return_[] $returns
     */
    private function matchAlwaysReturnConstFetch(array $returns) : ?Type
    {
        $classConstFetchTypes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof ClassConstFetch && !$return->expr instanceof ConstFetch) {
                return null;
            }
            $classConstFetchTypes[] = $this->nodeTypeResolver->getType($return->expr);
        }
        return $this->typeFactory->createMixedPassedOrUnionType($classConstFetchTypes);
    }
}
