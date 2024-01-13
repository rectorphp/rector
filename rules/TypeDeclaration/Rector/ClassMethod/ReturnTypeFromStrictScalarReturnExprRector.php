<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictScalarReturnTypeAnalyzer;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202401\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector\ReturnTypeFromStrictScalarReturnExprRectorTest
 */
final class ReturnTypeFromStrictScalarReturnExprRector extends AbstractScopeAwareRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictScalarReturnTypeAnalyzer
     */
    private $strictScalarReturnTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var string
     */
    public const HARD_CODED_ONLY = 'hard_coded_only';
    /**
     * @var bool
     */
    private $hardCodedOnly = \false;
    public function __construct(StrictScalarReturnTypeAnalyzer $strictScalarReturnTypeAnalyzer, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, StaticTypeMapper $staticTypeMapper)
    {
        $this->strictScalarReturnTypeAnalyzer = $strictScalarReturnTypeAnalyzer;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change return type based on strict scalar returns - string, int, float or bool', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function foo($value)
    {
        if ($value) {
            return 'yes';
        }

        return 'no';
    }

    public function bar(string $value)
    {
        return strlen($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function foo($value): string
    {
        if ($value) {
            return 'yes';
        }

        return 'no';
    }

    public function bar(string $value): int
    {
        return strlen($value);
    }
}
CODE_SAMPLE
, [\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector::HARD_CODED_ONLY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        // already added → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        $scalarReturnType = $this->strictScalarReturnTypeAnalyzer->matchAlwaysScalarReturnType($node, $this->hardCodedOnly);
        if (!$scalarReturnType instanceof Type) {
            return null;
        }
        // skip null as often placeholder value and not an only type
        if ($scalarReturnType instanceof NullType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($scalarReturnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        if ($returnTypeNode instanceof UnionType) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    public function configure(array $configuration) : void
    {
        $hardCodedOnly = $configuration[self::HARD_CODED_ONLY] ?? \false;
        Assert::boolean($hardCodedOnly);
        $this->hardCodedOnly = $hardCodedOnly;
    }
}
