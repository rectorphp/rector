<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictNativeFunctionReturnTypeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeFuncCallRector\ReturnTypeFromStrictNativeFuncCallRectorTest
 */
final class ReturnTypeFromStrictNativeFuncCallRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictNativeFunctionReturnTypeAnalyzer
     */
    private $strictNativeFunctionReturnTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(StrictNativeFunctionReturnTypeAnalyzer $strictNativeFunctionReturnTypeAnalyzer, TypeFactory $typeFactory)
    {
        $this->strictNativeFunctionReturnTypeAnalyzer = $strictNativeFunctionReturnTypeAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict return type based native function return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return strlen('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): int
    {
        return strlen('value');
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
        return [ClassMethod::class, Closure::class, Function_::class];
    }
    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        $nativeFuncCalls = $this->strictNativeFunctionReturnTypeAnalyzer->matchAlwaysReturnNativeFuncCalls($node);
        if ($nativeFuncCalls === null) {
            return null;
        }
        $funcCallTypes = [];
        foreach ($nativeFuncCalls as $nativeFuncCall) {
            $funcCallTypes[] = $this->getType($nativeFuncCall);
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionType($funcCallTypes);
        if ($returnType instanceof MixedType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
}
