<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\LNumber;

use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Util\StringUtils;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector\AddLiteralSeparatorToNumberRectorTest
 *
 * Taking the most generic use case to the account: https://wiki.php.net/rfc/numeric_literal_separator#should_it_be_the_role_of_an_ide_to_group_digits
 * The final check should be done manually
 */
final class AddLiteralSeparatorToNumberRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const LIMIT_VALUE = 'limit_value';
    /**
     * @var int
     */
    private const GROUP_SIZE = 3;
    /**
     * @var int
     */
    private const DEFAULT_LIMIT_VALUE = 1000000;
    /**
     * @var int
     */
    private $limitValue = self::DEFAULT_LIMIT_VALUE;
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $limitValue = $configuration[self::LIMIT_VALUE] ?? self::DEFAULT_LIMIT_VALUE;
        Assert::integer($limitValue);
        $this->limitValue = $limitValue;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add "_" as thousands separator in numbers for higher or equals to limitValue config', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 500000;
        $float = 1000500.001;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 500_000;
        $float = 1_000_500.001;
    }
}
CODE_SAMPLE
, [self::LIMIT_VALUE => 1000000])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [LNumber::class, DNumber::class];
    }
    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(Node $node) : ?Node
    {
        $rawValue = $node->getAttribute(AttributeKey::RAW_VALUE);
        if ($this->shouldSkip($node, $rawValue)) {
            return null;
        }
        if (\strpos((string) $rawValue, '.') !== \false) {
            [$mainPart, $decimalPart] = \explode('.', (string) $rawValue);
            $chunks = $this->strSplitNegative($mainPart, self::GROUP_SIZE);
            $literalSeparatedNumber = \implode('_', $chunks) . '.' . $decimalPart;
        } else {
            $chunks = $this->strSplitNegative($rawValue, self::GROUP_SIZE);
            $literalSeparatedNumber = \implode('_', $chunks);
            // PHP converts: (string) 1000.0 -> "1000"!
            if (\is_float($node->value)) {
                $literalSeparatedNumber .= '.0';
            }
        }
        // this cannot be integer directly to $node->value, as PHPStan sees it as error type
        // @see https://github.com/rectorphp/rector/issues/7454
        $node->setAttribute(AttributeKey::RAW_VALUE, $literalSeparatedNumber);
        $node->setAttribute(AttributeKey::REPRINT_RAW_VALUE, \true);
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::LITERAL_SEPARATOR;
    }
    /**
     * @param \PhpParser\Node\Scalar\LNumber|\PhpParser\Node\Scalar\DNumber $node
     * @param mixed $rawValue
     */
    private function shouldSkip($node, $rawValue) : bool
    {
        if (!\is_string($rawValue)) {
            return \true;
        }
        // already contains separator
        if (\strpos($rawValue, '_') !== \false) {
            return \true;
        }
        if ($node->value < $this->limitValue) {
            return \true;
        }
        $kind = $node->getAttribute(AttributeKey::KIND);
        if (\in_array($kind, [LNumber::KIND_BIN, LNumber::KIND_OCT, LNumber::KIND_HEX], \true)) {
            return \true;
        }
        // e+/e-
        if (StringUtils::isMatch($rawValue, '#e#i')) {
            return \true;
        }
        // too short
        return \strlen($rawValue) <= self::GROUP_SIZE;
    }
    /**
     * @return string[]
     */
    private function strSplitNegative(string $string, int $length) : array
    {
        $inversed = \strrev($string);
        /** @var string[] $chunks */
        $chunks = \str_split($inversed, $length);
        $chunks = \array_reverse($chunks);
        foreach ($chunks as $key => $chunk) {
            $chunks[$key] = \strrev($chunk);
        }
        return $chunks;
    }
}
