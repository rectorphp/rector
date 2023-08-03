<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\ConstExpr;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;
class DoctrineConstExprStringNode extends \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode
{
    use NodeAttributes;
    /** @var string */
    public $value;
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->value = $value;
    }
    public function __toString() : string
    {
        return self::escape($this->value);
    }
    public static function unescape(string $value) : string
    {
        // from https://github.com/doctrine/annotations/blob/a9ec7af212302a75d1f92fa65d3abfbd16245a2a/lib/Doctrine/Common/Annotations/DocLexer.php#L103-L107
        return str_replace('""', '"', substr($value, 1, strlen($value) - 2));
    }
    private static function escape(string $value) : string
    {
        // from https://github.com/phpstan/phpdoc-parser/issues/205#issuecomment-1662323656
        return sprintf('"%s"', str_replace('"', '""', $value));
    }
}
