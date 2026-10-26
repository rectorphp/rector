<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_using_values_null_as_an_array_offset_and_when_calling_array_key_exists
 * @see \Rector\Tests\Php85\Rector\FuncCall\ArrayKeyExistsNullToEmptyStringRector\ArrayKeyExistsNullToEmptyStringRectorTest
 */
final class ArrayKeyExistsNullToEmptyStringRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Replace null key in array_key_exists with empty string', [new CodeSample(<<<'CODE_SAMPLE'
array_key_exists(null, $array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_key_exists('', $array);
CODE_SAMPLE
)]);
    }
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
        if (!$this->isName($node, 'array_key_exists')) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) !== 2) {
            return null;
        }
        $argPosition = $this->argsAnalyzer->resolveArgPosition($args, 'key', 0);
        $activeArg = $args[$argPosition];
        if (!$this->getType($activeArg->value)->isNull()->yes()) {
            return null;
        }
        $activeArg->value = new String_('');
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_85;
    }
}
