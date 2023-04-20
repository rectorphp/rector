<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\ConstExpr;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function addcslashes;
use function assert;
use function dechex;
use function ord;
use function preg_replace_callback;
use function sprintf;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;
class QuoteAwareConstExprStringNode extends \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode implements \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
{
    public const SINGLE_QUOTED = 1;
    public const DOUBLE_QUOTED = 2;
    use NodeAttributes;
    /** @var self::SINGLE_QUOTED|self::DOUBLE_QUOTED */
    public $quoteType;
    /**
     * @param self::SINGLE_QUOTED|self::DOUBLE_QUOTED $quoteType
     */
    public function __construct(string $value, int $quoteType)
    {
        parent::__construct($value);
        $this->quoteType = $quoteType;
    }
    public function __toString() : string
    {
        if ($this->quoteType === self::SINGLE_QUOTED) {
            // from https://github.com/nikic/PHP-Parser/blob/0ffddce52d816f72d0efc4d9b02e276d3309ef01/lib/PhpParser/PrettyPrinter/Standard.php#L1007
            return sprintf("'%s'", addcslashes($this->value, '\'\\'));
        }
        // from https://github.com/nikic/PHP-Parser/blob/0ffddce52d816f72d0efc4d9b02e276d3309ef01/lib/PhpParser/PrettyPrinter/Standard.php#L1010-L1040
        return sprintf('"%s"', $this->escapeDoubleQuotedString());
    }
    private function escapeDoubleQuotedString() : string
    {
        $quote = '"';
        $escaped = addcslashes($this->value, "\n\r\t\f\v\$" . $quote . '\\');
        // Escape control characters and non-UTF-8 characters.
        // Regex based on https://stackoverflow.com/a/11709412/385378.
        $regex = '/(
              [\\x00-\\x08\\x0E-\\x1F] # Control characters
            | [\\xC0-\\xC1] # Invalid UTF-8 Bytes
            | [\\xF5-\\xFF] # Invalid UTF-8 Bytes
            | \\xE0(?=[\\x80-\\x9F]) # Overlong encoding of prior code point
            | \\xF0(?=[\\x80-\\x8F]) # Overlong encoding of prior code point
            | [\\xC2-\\xDF](?![\\x80-\\xBF]) # Invalid UTF-8 Sequence Start
            | [\\xE0-\\xEF](?![\\x80-\\xBF]{2}) # Invalid UTF-8 Sequence Start
            | [\\xF0-\\xF4](?![\\x80-\\xBF]{3}) # Invalid UTF-8 Sequence Start
            | (?<=[\\x00-\\x7F\\xF5-\\xFF])[\\x80-\\xBF] # Invalid UTF-8 Sequence Middle
            | (?<![\\xC2-\\xDF]|[\\xE0-\\xEF]|[\\xE0-\\xEF][\\x80-\\xBF]|[\\xF0-\\xF4]|[\\xF0-\\xF4][\\x80-\\xBF]|[\\xF0-\\xF4][\\x80-\\xBF]{2})[\\x80-\\xBF] # Overlong Sequence
            | (?<=[\\xE0-\\xEF])[\\x80-\\xBF](?![\\x80-\\xBF]) # Short 3 byte sequence
            | (?<=[\\xF0-\\xF4])[\\x80-\\xBF](?![\\x80-\\xBF]{2}) # Short 4 byte sequence
            | (?<=[\\xF0-\\xF4][\\x80-\\xBF])[\\x80-\\xBF](?![\\x80-\\xBF]) # Short 4 byte sequence (2)
        )/x';
        return preg_replace_callback($regex, static function ($matches) {
            assert(strlen($matches[0]) === 1);
            $hex = dechex(ord($matches[0]));
            return '\\x' . str_pad($hex, 2, '0', STR_PAD_LEFT);
        }, $escaped);
    }
}
