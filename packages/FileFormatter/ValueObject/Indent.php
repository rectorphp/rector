<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObject;

use RectorPrefix20220209\Nette\Utils\Strings;
use Rector\FileFormatter\Exception\InvalidIndentSizeException;
use Rector\FileFormatter\Exception\InvalidIndentStringException;
use Rector\FileFormatter\Exception\InvalidIndentStyleException;
use Rector\FileFormatter\Exception\ParseIndentException;
use Stringable;
/**
 * @see \Rector\Tests\FileFormatter\ValueObject\IndentTest
 */
final class Indent
{
    /**
     * @var array<string, string>
     */
    public const CHARACTERS = [self::SPACE => ' ', self::TAB => "\t"];
    /**
     * @var string
     */
    private const SPACE = 'space';
    /**
     * @var string
     */
    private const TAB = 'tab';
    /**
     * @see https://regex101.com/r/A2XiaF/1
     * @var string
     */
    private const VALID_INDENT_REGEX = '#^( *|\\t+)$#';
    /**
     * @var int
     */
    private const MINIMUM_SIZE = 1;
    /**
     * @see https://regex101.com/r/3HFEjX/1
     * @var string
     */
    private const PARSE_INDENT_REGEX = '/^(?P<indent>( +|\\t+)).*/m';
    /**
     * @readonly
     * @var string
     */
    private $string;
    private function __construct(string $string)
    {
        $this->string = $string;
    }
    public function __toString() : string
    {
        return $this->string;
    }
    public static function fromString(string $content) : self
    {
        $match = \RectorPrefix20220209\Nette\Utils\Strings::match($content, self::VALID_INDENT_REGEX);
        if ($match === null) {
            throw \Rector\FileFormatter\Exception\InvalidIndentStringException::fromString($content);
        }
        return new self($content);
    }
    public static function createSpaceWithSize(int $size) : self
    {
        return self::fromSizeAndStyle($size, self::SPACE);
    }
    public static function createTab() : self
    {
        return self::fromSizeAndStyle(1, self::TAB);
    }
    public static function fromSizeAndStyle(int $size, string $style) : self
    {
        if ($size < self::MINIMUM_SIZE) {
            throw \Rector\FileFormatter\Exception\InvalidIndentSizeException::fromSizeAndMinimumSize($size, self::MINIMUM_SIZE);
        }
        if (!\array_key_exists($style, self::CHARACTERS)) {
            throw \Rector\FileFormatter\Exception\InvalidIndentStyleException::fromStyleAndAllowedStyles($style, \array_keys(self::CHARACTERS));
        }
        $value = \str_repeat(self::CHARACTERS[$style], $size);
        return new self($value);
    }
    public static function fromContent(string $content) : self
    {
        $match = \RectorPrefix20220209\Nette\Utils\Strings::match($content, self::PARSE_INDENT_REGEX);
        if (isset($match['indent'])) {
            return self::fromString($match['indent']);
        }
        throw \Rector\FileFormatter\Exception\ParseIndentException::fromString($content);
    }
    public function getIndentSize() : int
    {
        return \strlen($this->string);
    }
    public function getIndentStyle() : string
    {
        return $this->startsWithSpace() ? self::SPACE : self::TAB;
    }
    public function getIndentStyleCharacter() : string
    {
        return $this->startsWithSpace() ? self::CHARACTERS[self::SPACE] : self::CHARACTERS[self::TAB];
    }
    private function startsWithSpace() : bool
    {
        return \strncmp($this->string, ' ', \strlen(' ')) === 0;
    }
}
