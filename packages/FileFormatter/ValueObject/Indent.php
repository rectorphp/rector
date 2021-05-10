<?php

declare(strict_types=1);

namespace Rector\FileFormatter\ValueObject;

use Nette\Utils\Strings;
use Rector\FileFormatter\Exception\InvalidIndentSizeException;
use Rector\FileFormatter\Exception\InvalidIndentStringException;
use Rector\FileFormatter\Exception\InvalidIndentStyleException;
use Rector\FileFormatter\Exception\ParseIndentException;

/**
 * @see \Rector\Tests\FileFormatter\ValueObject\IndentTest
 */
final class Indent
{
    /**
     * @var string[]
     */
    public const CHARACTERS = [
        self::SPACE => ' ',
        self::TAB => "\t",
    ];

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
    private const VALID_INDENT_REGEX = '/^( *|\t+)$/';

    /**
     * @var int
     */
    private const MINIMUM_SIZE = 1;

    /**
     * @see https://regex101.com/r/3HFEjX/1
     * @var string
     */
    private const PARSE_INDENT_REGEX = '/^(?P<indent>( +|\t+)).*/m';

    private function __construct(
        private string $string
    ) {
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public static function fromString(string $string): self
    {
        $validIndent = preg_match(self::VALID_INDENT_REGEX, $string);

        if ($validIndent !== 1) {
            throw InvalidIndentStringException::fromString($string);
        }

        return new self($string);
    }

    public static function createSpaceWithSize(int $size): self
    {
        return self::fromSizeAndStyle($size, self::SPACE);
    }

    public static function createTabWithSize(int $size): self
    {
        return self::fromSizeAndStyle($size, self::TAB);
    }

    public static function fromSizeAndStyle(int $size, string $style): self
    {
        if ($size < self::MINIMUM_SIZE) {
            throw InvalidIndentSizeException::fromSizeAndMinimumSize($size, self::MINIMUM_SIZE);
        }

        if (! array_key_exists($style, self::CHARACTERS)) {
            throw InvalidIndentStyleException::fromStyleAndAllowedStyles($style, array_keys(self::CHARACTERS));
        }

        $value = str_repeat(self::CHARACTERS[$style], $size);

        return new self($value);
    }

    public static function fromContent(string $string): self
    {
        $validIndent = preg_match(self::PARSE_INDENT_REGEX, $string, $match);
        if ($validIndent === 1) {
            return self::fromString($match['indent']);
        }

        throw ParseIndentException::fromString($string);
    }

    public function getIndentSize(): int
    {
        return strlen($this->string);
    }

    public function getIndentStyle(): string
    {
        return $this->startsWithSpace() ? self::SPACE : self::TAB;
    }

    public function getIndentStyleCharacter(): string
    {
        return $this->startsWithSpace() ? self::CHARACTERS[self::SPACE] : self::CHARACTERS[self::TAB];
    }

    private function startsWithSpace(): bool
    {
        return Strings::startsWith($this->string, ' ');
    }
}
