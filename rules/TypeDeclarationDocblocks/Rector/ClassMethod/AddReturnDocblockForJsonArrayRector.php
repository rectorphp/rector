<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\Enum\NetteClassName;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForJsonArrayRector\AddReturnDocblockForJsonArrayRectorTest
 */
final class AddReturnDocblockForJsonArrayRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReturnNodeFinder $returnNodeFinder;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ReturnNodeFinder $returnNodeFinder, PhpDocTypeChanger $phpDocTypeChanger, ValueResolver $valueResolver, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->valueResolver = $valueResolver;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return docblock for array based on return of json_decode() return array', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function provide(string $contents): array
    {
        return json_decode($contents, true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return array<string, mixed>
     */
    public function provide(string $contents): array
    {
        return json_decode($contents, true);
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        // definitely not an array return
        if ($node->returnType instanceof Node && !$this->isName($node->returnType, 'array')) {
            return null;
        }
        $onlyReturnWithExpr = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$onlyReturnWithExpr instanceof Return_) {
            return null;
        }
        $returnedExpr = $onlyReturnWithExpr->expr;
        if (!$returnedExpr instanceof Expr) {
            return null;
        }
        if (!$this->isJsonDecodeToArray($returnedExpr)) {
            return null;
        }
        $classMethodDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($classMethodDocInfo->getReturnTagValue())) {
            return null;
        }
        $hasChanged = $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, new ArrayType(new StringType(), new MixedType()));
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isJsonDecodeToArray(Expr $expr): bool
    {
        if ($expr instanceof FuncCall) {
            if (!$this->isName($expr, 'json_decode')) {
                return \false;
            }
            if ($expr->isFirstClassCallable()) {
                return \false;
            }
            $arg = $expr->getArg('associative', 1);
            if (!$arg instanceof Arg) {
                return \false;
            }
            return $this->valueResolver->isTrue($arg->value);
        }
        if ($expr instanceof StaticCall) {
            if (!$this->isName($expr->class, NetteClassName::JSON)) {
                return \false;
            }
            if (!$this->isName($expr->name, 'decode')) {
                return \false;
            }
            if ($expr->isFirstClassCallable()) {
                return \false;
            }
            $arg = $expr->getArg('forceArrays', 1);
            if (!$arg instanceof Arg) {
                return \false;
            }
            return $this->valueResolver->isTrue($arg->value);
        }
        return \false;
    }
}
