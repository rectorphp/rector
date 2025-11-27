<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_context_parameter_for_finfo_buffer
 * @see \Rector\Tests\Php85\Rector\FuncCall\RemoveFinfoBufferContextArgRector\RemoveFinfoBufferContextArgRectorTest
 */
final class RemoveFinfoBufferContextArgRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove argument by position by function name', [new CodeSample(<<<'CODE_SAMPLE'
finfo_buffer($finfo, $fileContents, FILEINFO_NONE, []);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
finfo_buffer($finfo, $fileContents, FILEINFO_NONE);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class];
    }
    /**
     * @param MethodCall|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // Cannot handle variadic args
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($node instanceof FuncCall && !$this->isName($node->name, 'finfo_buffer')) {
            return null;
        }
        $objectType = new ObjectType('finfo');
        if ($node instanceof MethodCall && (!$this->nodeTypeResolver->isObjectType($node->var, $objectType) || !$this->isName($node->name, 'buffer'))) {
            return null;
        }
        if ($this->removeContextArg($node)) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_FINFO_BUFFER_CONTEXT;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall $callLike
     */
    private function removeContextArg($callLike): bool
    {
        // In `finfo::buffer` method calls, the first parameter, compared to `finfo_buffer`, does not exist.
        $methodArgCorrection = 0;
        if ($callLike instanceof MethodCall) {
            $methodArgCorrection = -1;
        }
        if (count($callLike->args) <= 2 + $methodArgCorrection) {
            return \false;
        }
        $args = $callLike->getArgs();
        // Argument 3 ($flags) and argument 4 ($context) are optional, thus named parameters must be considered
        if (!$this->argsAnalyzer->hasNamedArg($args)) {
            if (count($args) < 4 + $methodArgCorrection) {
                return \false;
            }
            unset($callLike->args[3 + $methodArgCorrection]);
            // update indexed to make printer work as expected
            $callLike->args = array_values($callLike->args);
            return \true;
        }
        // process named arguments
        foreach ($args as $position => $arg) {
            if ($arg->name instanceof Identifier && $this->isName($arg->name, 'context')) {
                unset($callLike->args[$position]);
                // update indexed to make printer work as expected
                $callLike->args = array_values($callLike->args);
                return \true;
            }
        }
        return \false;
    }
}
