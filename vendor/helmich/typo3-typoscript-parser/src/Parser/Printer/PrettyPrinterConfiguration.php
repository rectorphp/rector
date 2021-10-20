<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Printer;

use InvalidArgumentException;
use LogicException;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * PrinterConfiguration
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\PrettyPrinterConfiguration
 */
final class PrettyPrinterConfiguration
{
    /**
     * @var string
     */
    public const INDENTATION_STYLE_SPACES = 'spaces';
    /**
     * @var string
     */
    public const INDENTATION_STYLE_TABS = 'tabs';
    /**
     * @var bool
     */
    private $addClosingGlobal = \false;
    /**
     * @var bool
     */
    private $includeEmptyLineBreaks = \false;
    /**
     * @var int
     */
    private $indentationSize = 4;
    /**
     * @var string
     */
    private $indentationStyle = self::INDENTATION_STYLE_SPACES;
    private function __construct()
    {
    }
    public static function create() : self
    {
        return new self();
    }
    public function withTabs() : self
    {
        $clone = clone $this;
        $clone->indentationStyle = self::INDENTATION_STYLE_TABS;
        $clone->indentationSize = 1;
        return $clone;
    }
    public function withSpaceIndentation(int $size) : self
    {
        $clone = clone $this;
        $clone->indentationStyle = self::INDENTATION_STYLE_SPACES;
        $clone->indentationSize = $size;
        return $clone;
    }
    public function withClosingGlobalStatement() : self
    {
        $clone = clone $this;
        $clone->addClosingGlobal = \true;
        return $clone;
    }
    public function withEmptyLineBreaks() : self
    {
        $clone = clone $this;
        $clone->includeEmptyLineBreaks = \true;
        return $clone;
    }
    public function shouldAddClosingGlobal() : bool
    {
        return $this->addClosingGlobal;
    }
    public function shouldIncludeEmptyLineBreaks() : bool
    {
        return $this->includeEmptyLineBreaks;
    }
    public function getIndentation() : string
    {
        if ($this->indentationStyle === self::INDENTATION_STYLE_TABS) {
            return "\t";
        }
        return \str_repeat(' ', $this->indentationSize);
    }
}
