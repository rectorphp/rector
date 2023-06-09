<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeAnalyzer\StrictReturnClassConstReturnTypeAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector\ReturnTypeFromStrictConstantReturnRectorTest
 */
final class ReturnTypeFromStrictConstantReturnRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\StrictReturnClassConstReturnTypeAnalyzer
     */
    private $strictReturnClassConstReturnTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(StrictReturnClassConstReturnTypeAnalyzer $strictReturnClassConstReturnTypeAnalyzer, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->strictReturnClassConstReturnTypeAnalyzer = $strictReturnClassConstReturnTypeAnalyzer;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $matchedType = $this->strictReturnClassConstReturnTypeAnalyzer->matchAlwaysReturnConstFetch($node);
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
}
