<?php

declare (strict_types=1);
namespace Rector\Assert\Rector\ClassMethod;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\Assert\Enum\AssertClassName;
use Rector\Assert\NodeAnalyzer\ExistingAssertStaticCallResolver;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @experimental Check generic array key/value types in runtime with assert. Generics for impatient people.
 *
 * @see \Rector\Tests\Assert\Rector\ClassMethod\AddAssertArrayFromClassMethodDocblockRector\AddAssertArrayFromClassMethodDocblockRectorTest
 */
final class AddAssertArrayFromClassMethodDocblockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ExistingAssertStaticCallResolver $existingAssertStaticCallResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ExistingAssertStaticCallResolver $existingAssertStaticCallResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->existingAssertStaticCallResolver = $existingAssertStaticCallResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add key and value assert based on docblock @param type declarations', [new CodeSample(<<<'CODE_SAMPLE'
<?php

namespace RectorPrefix202509;

class SomeClass
{
    /**
     * @param int[] $items
     */
    public function run(array $items)
    {
    }
}
\class_alias('SomeClass', 'SomeClass', \false);

CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

namespace RectorPrefix202509;

use RectorPrefix202509\Webmozart\Assert\Assert;
class SomeClass
{
    /**
     * @param int[] $items
     */
    public function run(array $items)
    {
        Assert::allInteger($items);
    }
}
\class_alias('SomeClass', 'SomeClass', \false);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        $scope = ScopeFetcher::fetch($node);
        if (!$scope->isInClass()) {
            return null;
        }
        if ($node->stmts === null || $node->isAbstract()) {
            return null;
        }
        $methodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$methodPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $paramTagValueNodes = $methodPhpDocInfo->getParamTagValueNodes();
        if ($paramTagValueNodes === []) {
            return null;
        }
        $assertStaticCallStmts = [];
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof Identifier) {
                continue;
            }
            // handle arrays only
            if ($param->type->name !== 'array') {
                continue;
            }
            if (!$param->var instanceof Variable) {
                continue;
            }
            $paramName = $param->var->name;
            if (!\is_string($paramName)) {
                continue;
            }
            $paramDocType = $methodPhpDocInfo->getParamType($paramName);
            if (!$paramDocType instanceof ArrayType) {
                continue;
            }
            // assert value
            if ($paramDocType->getItemType() instanceof IntegerType) {
                $assertStaticCallStmts[] = $this->createAssertExpression($param->var, 'allInteger');
            } elseif ($paramDocType->getItemType() instanceof StringType) {
                $assertStaticCallStmts[] = $this->createAssertExpression($param->var, 'allString');
            }
            // assert keys
            $arrayKeys = new FuncCall(new Name('array_keys'), [new Arg($param->var)]);
            if ($paramDocType->getKeyType() instanceof StringType) {
                $assertStaticCallStmts[] = $this->createAssertExpression($arrayKeys, 'allString');
            } elseif ($paramDocType->getKeyType() instanceof IntegerType) {
                $assertStaticCallStmts[] = $this->createAssertExpression($arrayKeys, 'allInteger');
            }
        }
        // filter existing assert to avoid duplication
        if ($assertStaticCallStmts === []) {
            return null;
        }
        $existingAssertCallHashes = $this->existingAssertStaticCallResolver->resolve($node);
        $assertStaticCallStmts = $this->filterOutExistingStaticCall($assertStaticCallStmts, $existingAssertCallHashes);
        if ($assertStaticCallStmts === []) {
            return null;
        }
        $node->stmts = \array_merge($assertStaticCallStmts, $node->stmts);
        return $node;
    }
    private function createAssertExpression(Expr $expr, string $methodName) : Expression
    {
        $staticCall = new StaticCall(new FullyQualified(AssertClassName::ASSERT), $methodName, [new Arg($expr)]);
        return new Expression($staticCall);
    }
    /**
     * @param Expression[] $assertStaticCallStmts
     * @param string[] $existingAssertCallHashes
     * @return Expression[]
     */
    private function filterOutExistingStaticCall(array $assertStaticCallStmts, array $existingAssertCallHashes) : array
    {
        $standard = new Standard();
        return \array_filter($assertStaticCallStmts, function (Expression $assertStaticCallExpression) use($standard, $existingAssertCallHashes) : bool {
            $currentStaticCallHash = $standard->prettyPrintExpr($assertStaticCallExpression->expr);
            return !\in_array($currentStaticCallHash, $existingAssertCallHashes, \true);
        });
    }
}
