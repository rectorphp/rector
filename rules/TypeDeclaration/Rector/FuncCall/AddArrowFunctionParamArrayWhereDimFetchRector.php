<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Type\ArrayType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\Enum\NativeFuncCallPositions;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FuncCall\AddArrowFunctionParamArrayWhereDimFetchRector\AddArrowFunctionParamArrayWhereDimFetchRectorTest
 */
final class AddArrowFunctionParamArrayWhereDimFetchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add closure and arrow function param array type, if dim fetch is used inside', [new CodeSample(<<<'CODE_SAMPLE'
$array = [['name' => 'John']];

$result = array_map(fn ($item) => $item['name'], $array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = [['name' => 'John']];

$result = array_map(fn (array $item) => $item['name'], $array);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (count($node->getArgs()) !== 2) {
            return null;
        }
        $hasChanged = \false;
        foreach (NativeFuncCallPositions::ARRAY_AND_CALLBACK_POSITIONS as $functionName => $positions) {
            if (!$this->isName($node, $functionName)) {
                continue;
            }
            $callbackPosition = $positions['callback'];
            $closureExpr = $node->getArgs()[$callbackPosition]->value;
            if (!$closureExpr instanceof ArrowFunction && !$closureExpr instanceof Closure) {
                continue;
            }
            $arrayPosition = $positions['array'];
            $closureItems = $node->getArgs()[$arrayPosition]->value;
            $isArrayVariableNames = $this->resolveIsArrayVariables($closureExpr);
            $instanceofVariableNames = $this->resolveInstanceofVariables($closureExpr);
            $skippedVariableNames = array_merge($isArrayVariableNames, $instanceofVariableNames);
            $dimFetchVariableNames = $this->resolveDimFetchVariableNames($closureExpr);
            foreach ($closureExpr->getParams() as $closureParam) {
                if ($closureParam->type instanceof Node) {
                    // param is known already
                    continue;
                }
                // skip is_array() checked variables
                if ($this->isNames($closureParam->var, $skippedVariableNames)) {
                    continue;
                }
                if (!$this->isNames($closureParam->var, $dimFetchVariableNames)) {
                    continue;
                }
                $type = $this->getType($closureItems);
                if ($type instanceof ArrayType && $type->getItemType()->isObject()->yes()) {
                    continue;
                }
                $hasChanged = \true;
                $closureParam->type = new Identifier('array');
            }
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $closureExpr
     */
    private function resolveDimFetchVariableNames($closureExpr): array
    {
        $closureNodes = $closureExpr instanceof ArrowFunction ? [$closureExpr->expr] : $closureExpr->stmts;
        /** @var ArrayDimFetch[] $arrayDimFetches */
        $arrayDimFetches = $this->betterNodeFinder->findInstancesOfScoped($closureNodes, ArrayDimFetch::class);
        $usedDimFetchVariableNames = [];
        foreach ($arrayDimFetches as $arrayDimFetch) {
            if ($arrayDimFetch->var instanceof Variable) {
                $type = $this->nodeTypeResolver->getNativeType($arrayDimFetch->var);
                if ($type->isString()->yes()) {
                    continue;
                }
                $usedDimFetchVariableNames[] = (string) $this->getName($arrayDimFetch->var);
            }
        }
        return $usedDimFetchVariableNames;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $closureExpr
     */
    private function resolveIsArrayVariables($closureExpr): array
    {
        $closureNodes = $closureExpr instanceof ArrowFunction ? [$closureExpr->expr] : $closureExpr->stmts;
        /** @var FuncCall[] $funcCalls */
        $funcCalls = $this->betterNodeFinder->findInstancesOfScoped($closureNodes, FuncCall::class);
        $variableNames = [];
        foreach ($funcCalls as $funcCall) {
            if (!$this->isName($funcCall, 'is_array')) {
                continue;
            }
            $firstArgExpr = $funcCall->getArgs()[0]->value;
            if (!$firstArgExpr instanceof Variable) {
                continue;
            }
            $variableNames[] = (string) $this->getName($firstArgExpr);
        }
        return $variableNames;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $closureExpr
     */
    private function resolveInstanceofVariables($closureExpr): array
    {
        $closureNodes = $closureExpr instanceof ArrowFunction ? [$closureExpr->expr] : $closureExpr->stmts;
        /** @var Instanceof_[] $instanceOfs */
        $instanceOfs = $this->betterNodeFinder->findInstancesOfScoped($closureNodes, Instanceof_::class);
        $variableNames = [];
        foreach ($instanceOfs as $instanceOf) {
            if (!$instanceOf->expr instanceof Variable) {
                continue;
            }
            $variableNames[] = (string) $this->getName($instanceOf->expr);
        }
        return $variableNames;
    }
}
