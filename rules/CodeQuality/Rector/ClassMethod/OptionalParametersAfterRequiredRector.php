<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add null default value when a required parameter follows an optional one', [new CodeSample(<<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required = null)
    {
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    public function refactor(Node $node)
    {
        if ($node->params === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $key => $param) {
            if ($param->default instanceof Expr) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            $previousParam = $node->params[$key - 1] ?? null;
            if ($previousParam instanceof Param && $previousParam->default instanceof Expr) {
                $hasChanged = \true;
                $param->default = new ConstFetch(new Name('null'));
                $paramType = $param->type;
                if (!$paramType instanceof Node) {
                    continue;
                }
                if ($paramType instanceof NullableType) {
                    continue;
                }
                if ($paramType instanceof UnionType || $paramType instanceof IntersectionType) {
                    foreach ($paramType->types as $unionedType) {
                        if ($unionedType instanceof Identifier && $this->isName($unionedType, 'null')) {
                            continue 2;
                        }
                    }
                    $paramType->types[] = new Identifier('null');
                    continue;
                }
                if ($paramType instanceof ComplexType) {
                    continue;
                }
                $param->type = new NullableType($paramType);
            }
        }
        return $hasChanged ? $node : null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
}
