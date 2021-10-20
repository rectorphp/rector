<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\LNumber;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/numeric_literal_separator
 * @changelog https://github.com/nikic/PHP-Parser/pull/615
 * @see \Rector\Tests\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector\AddLiteralSeparatorToNumberRectorTest
 * @changelog https://twitter.com/seldaek/status/1329064983120982022
 *
 * Taking the most generic use case to the account: https://wiki.php.net/rfc/numeric_literal_separator#should_it_be_the_role_of_an_ide_to_group_digits
 * The final check should be done manually
 */
final class AddLiteralSeparatorToNumberRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface, \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
    private $limitValue = 1000000;
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $limitValue = $configuration[self::LIMIT_VALUE] ?? 1000000;
        \RectorPrefix20211020\Webmozart\Assert\Assert::integer($limitValue);
        $this->limitValue = $limitValue;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add "_" as thousands separator in numbers for higher or equals to limitValue config', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Scalar\LNumber::class, \PhpParser\Node\Scalar\DNumber::class];
    }
    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $numericValueAsString = (string) $node->value;
        if ($this->shouldSkip($node, $numericValueAsString)) {
            return null;
        }
        if (\strpos($numericValueAsString, '.') !== \false) {
            [$mainPart, $decimalPart] = \explode('.', $numericValueAsString);
            $chunks = $this->strSplitNegative($mainPart, self::GROUP_SIZE);
            $literalSeparatedNumber = \implode('_', $chunks) . '.' . $decimalPart;
        } else {
            $chunks = $this->strSplitNegative($numericValueAsString, self::GROUP_SIZE);
            $literalSeparatedNumber = \implode('_', $chunks);
            // PHP converts: (string) 1000.0 -> "1000"!
            if (\is_float($node->value)) {
                $literalSeparatedNumber .= '.0';
            }
        }
        $node->value = $literalSeparatedNumber;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::LITERAL_SEPARATOR;
    }
    /**
     * @param \PhpParser\Node\Scalar\LNumber|\PhpParser\Node\Scalar\DNumber $node
     */
    private function shouldSkip($node, string $numericValueAsString) : bool
    {
        /** @var int $startToken */
        $startToken = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION);
        $file = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::FILE);
        // new node
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            return \true;
        }
        $oldTokens = $file->getOldTokens();
        foreach ($oldTokens[$startToken] as $token) {
            if (!\is_string($token)) {
                continue;
            }
            if (\strpos($token, '_') === \false) {
                continue;
            }
            return \true;
        }
        if ($numericValueAsString < $this->limitValue) {
            return \true;
        }
        // already separated
        if (\strpos($numericValueAsString, '_') !== \false) {
            return \true;
        }
        $kind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if (\in_array($kind, [\PhpParser\Node\Scalar\LNumber::KIND_BIN, \PhpParser\Node\Scalar\LNumber::KIND_OCT, \PhpParser\Node\Scalar\LNumber::KIND_HEX], \true)) {
            return \true;
        }
        // e+/e-
        if (\RectorPrefix20211020\Nette\Utils\Strings::match($numericValueAsString, '#e#i')) {
            return \true;
        }
        // too short
        return \RectorPrefix20211020\Nette\Utils\Strings::length($numericValueAsString) <= self::GROUP_SIZE;
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
