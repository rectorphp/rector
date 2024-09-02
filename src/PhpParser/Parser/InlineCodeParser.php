<?php

declare (strict_types=1);
namespace Rector\PhpParser\Parser;

use RectorPrefix202409\Nette\Utils\FileSystem;
use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\Util\StringUtils;
final class InlineCodeParser
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var string
     * @see https://regex101.com/r/dwe4OW/1
     */
    private const PRESLASHED_DOLLAR_REGEX = '#\\\\\\$#';
    /**
     * @var string
     * @see https://regex101.com/r/tvwhWq/1
     */
    private const CURLY_BRACKET_WRAPPER_REGEX = "#'{(\\\$.*?)}'#";
    /**
     * @var string
     * @see https://regex101.com/r/TBlhoR/1
     */
    private const OPEN_PHP_TAG_REGEX = '#^\\<\\?php\\s+#';
    /**
     * @var string
     * @see https://regex101.com/r/TUWwKw/1/
     */
    private const ENDING_SEMI_COLON_REGEX = '#;(\\s+)?$#';
    /**
     * @var string
     * @see https://regex101.com/r/8fDjnR/1
     */
    private const VARIABLE_IN_SINGLE_QUOTED_REGEX = '#\'(?<variable>\\$.*)\'#U';
    /**
     * @var string
     * @see https://regex101.com/r/1lzQZv/1
     */
    private const BACKREFERENCE_NO_QUOTE_REGEX = '#(?<!")(?<backreference>\\\\\\d+)(?!")#';
    /**
     * @var string
     * @see https://regex101.com/r/nSO3Eq/1
     */
    private const BACKREFERENCE_NO_DOUBLE_QUOTE_START_REGEX = '#(?<!")(?<backreference>\\$\\d+)#';
    public function __construct(BetterStandardPrinter $betterStandardPrinter, \Rector\PhpParser\Parser\SimplePhpParser $simplePhpParser, ValueResolver $valueResolver)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->simplePhpParser = $simplePhpParser;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @api downgrade
     *
     * @return Stmt[]
     */
    public function parseFile(string $fileName) : array
    {
        $fileContent = FileSystem::read($fileName);
        return $this->parseCode($fileContent);
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $fileContent) : array
    {
        return $this->parseCode($fileContent);
    }
    public function stringify(Expr $expr) : string
    {
        if ($expr instanceof String_) {
            if (!StringUtils::isMatch($expr->value, self::BACKREFERENCE_NO_QUOTE_REGEX)) {
                return Strings::replace($expr->value, self::BACKREFERENCE_NO_DOUBLE_QUOTE_START_REGEX, static function (array $match) : string {
                    return '"' . $match['backreference'] . '"';
                });
            }
            return Strings::replace($expr->value, self::BACKREFERENCE_NO_QUOTE_REGEX, static function (array $match) : string {
                return '"\\' . $match['backreference'] . '"';
            });
        }
        if ($expr instanceof Encapsed) {
            return $this->resolveEncapsedValue($expr);
        }
        if ($expr instanceof Concat) {
            return $this->resolveConcatValue($expr);
        }
        return $this->betterStandardPrinter->print($expr);
    }
    /**
     * @return Stmt[]
     */
    private function parseCode(string $code) : array
    {
        // wrap code so php-parser can interpret it
        $code = StringUtils::isMatch($code, self::OPEN_PHP_TAG_REGEX) ? $code : '<?php ' . $code;
        $code = StringUtils::isMatch($code, self::ENDING_SEMI_COLON_REGEX) ? $code : $code . ';';
        return $this->simplePhpParser->parseString($code);
    }
    private function resolveEncapsedValue(Encapsed $encapsed) : string
    {
        $value = '';
        $isRequirePrint = \false;
        foreach ($encapsed->parts as $part) {
            $partValue = (string) $this->valueResolver->getValue($part);
            if (\substr_compare($partValue, "'", -\strlen("'")) === 0) {
                $isRequirePrint = \true;
                break;
            }
            $value .= $partValue;
        }
        $printedExpr = $isRequirePrint ? $this->betterStandardPrinter->print($encapsed) : $value;
        // remove "
        $printedExpr = \trim($printedExpr, '""');
        // use \$ → $
        $printedExpr = Strings::replace($printedExpr, self::PRESLASHED_DOLLAR_REGEX, '$');
        // use \'{$...}\' → $...
        return Strings::replace($printedExpr, self::CURLY_BRACKET_WRAPPER_REGEX, '$1');
    }
    private function resolveConcatValue(Concat $concat) : string
    {
        if ($concat->left instanceof Concat && $concat->right instanceof String_ && \strncmp($concat->right->value, '$', \strlen('$')) === 0) {
            $concat->right->value = '.' . $concat->right->value;
        }
        if ($concat->right instanceof String_ && \strncmp($concat->right->value, '($', \strlen('($')) === 0) {
            $concat->right->value .= '.';
        }
        $string = $this->stringify($concat->left) . $this->stringify($concat->right);
        return Strings::replace($string, self::VARIABLE_IN_SINGLE_QUOTED_REGEX, static function (array $match) {
            return $match['variable'];
        });
    }
}
