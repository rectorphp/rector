<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\DowngradeNamedArgumentRectorTest
 */
final class DowngradeNamedArgumentRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver
     */
    private $unnamedArgumentResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver $unnamedArgumentResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->unnamedArgumentResolver = $unnamedArgumentResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\FuncCall::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove named argument', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute(b: 100);
    }

    private function execute($a = null, $b = null)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute(null, 100);
    }

    private function execute($a = null, $b = null)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $args = $node->args;
        if ($this->shouldSkip($args)) {
            return null;
        }
        $this->removeNamedArguments($node, $args);
        return $node;
    }
    /**
     * @param Arg[] $args
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall $node
     */
    private function removeNamedArguments($node, array $args) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            $functionLikeReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
        } else {
            $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        }
        if (!$functionLikeReflection instanceof \PHPStan\Reflection\MethodReflection && !$functionLikeReflection instanceof \PHPStan\Reflection\FunctionReflection) {
            return null;
        }
        $unnamedArgs = $this->unnamedArgumentResolver->resolveFromReflection($functionLikeReflection, $args);
        $node->args = $unnamedArgs;
        return $node;
    }
    /**
     * @param mixed[]|Arg[] $args
     */
    private function shouldSkip(array $args) : bool
    {
        foreach ($args as $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if (!$arg->name instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
