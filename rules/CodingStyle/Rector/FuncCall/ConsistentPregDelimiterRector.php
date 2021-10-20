<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\ConsistentPregDelimiterRectorTest
 */
final class ConsistentPregDelimiterRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const DELIMITER = 'delimiter';
    /**
     * @var string
     * @see https://regex101.com/r/isdgEN/1
     *
     * For modifiers see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const INNER_REGEX = '#(?<content>.*?)(?<close>[imsxeADSUXJu]*)$#s';
    /**
     * @var string
     * @see https://regex101.com/r/EyXsV6/6
     */
    private const DOUBLE_QUOTED_REGEX = '#^"(?<delimiter>.{1}).{1,}\\k<delimiter>[imsxeADSUXJu]*"$#';
    /**
     * All with pattern as 1st argument
     * @var array<string, int>
     */
    private const FUNCTIONS_WITH_REGEX_PATTERN = ['preg_match' => 0, 'preg_replace_callback_array' => 0, 'preg_replace_callback' => 0, 'preg_replace' => 0, 'preg_match_all' => 0, 'preg_split' => 0, 'preg_grep' => 0];
    /**
     * All with pattern as 2st argument
     * @var array<string, array<string, int>>
     */
    private const STATIC_METHODS_WITH_REGEX_PATTERN = ['Nette\\Utils\\Strings' => ['match' => 1, 'matchAll' => 1, 'replace' => 1, 'split' => 1]];
    /**
     * @var string
     */
    private $delimiter = '#';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace PREG delimiter with configured one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('~value~', $value);
        preg_match_all('~value~im', $value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('#value#', $value);
        preg_match_all('#value#im', $value);
    }
}
CODE_SAMPLE
, [self::DELIMITER => '#'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->refactorFuncCall($node);
        }
        foreach (self::STATIC_METHODS_WITH_REGEX_PATTERN as $type => $methodsToPositions) {
            if (!$this->isObjectType($node->class, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            foreach ($methodsToPositions as $method => $position) {
                if (!$this->isName($node->name, $method)) {
                    continue;
                }
                if (!$node->args[$position] instanceof \PhpParser\Node\Arg) {
                    continue;
                }
                $this->refactorArgument($node->args[$position]);
                return $node;
            }
        }
        return null;
    }
    public function configure(array $configuration) : void
    {
        $this->delimiter = $configuration[self::DELIMITER] ?? '#';
    }
    private function refactorFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        foreach (self::FUNCTIONS_WITH_REGEX_PATTERN as $function => $position) {
            if (!$this->isName($funcCall, $function)) {
                continue;
            }
            if (!$funcCall->args[$position] instanceof \PhpParser\Node\Arg) {
                continue;
            }
            $this->refactorArgument($funcCall->args[$position]);
            return $funcCall;
        }
        return null;
    }
    private function refactorArgument(\PhpParser\Node\Arg $arg) : void
    {
        if (!$arg->value instanceof \PhpParser\Node\Scalar\String_) {
            return;
        }
        /** @var String_ $string */
        $string = $arg->value;
        $string->value = \RectorPrefix20211020\Nette\Utils\Strings::replace($string->value, self::INNER_REGEX, function (array $match) use(&$string) : string {
            $printedString = $this->betterStandardPrinter->print($string);
            if (\RectorPrefix20211020\Nette\Utils\Strings::match($printedString, self::DOUBLE_QUOTED_REGEX)) {
                $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_REGULAR_PATTERN, \true);
            }
            $innerPattern = $match['content'];
            $positionDelimiter = \strpos($innerPattern, $this->delimiter);
            if ($positionDelimiter > 0) {
                $innerPattern = \str_replace($this->delimiter, '\\' . $this->delimiter, $innerPattern);
            }
            // change delimiter
            if (\strlen($innerPattern) > 2 && $innerPattern[0] === $innerPattern[\strlen($innerPattern) - 1]) {
                $innerPattern[0] = $this->delimiter;
                $innerPattern[\strlen($innerPattern) - 1] = $this->delimiter;
            }
            return $innerPattern . $match['close'];
        });
    }
}
