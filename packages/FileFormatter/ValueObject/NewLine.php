<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObject;

use RectorPrefix20211231\Nette\Utils\Strings;
use const PHP_EOL;
use Rector\FileFormatter\Exception\InvalidNewLineStringException;
use Stringable;
/**
 * @see \Rector\Tests\FileFormatter\ValueObject\NewLineTest
 */
final class NewLine
{
    /**
     * @var string
     */
    public const LINE_FEED = 'lf';
    /**
     * @var string
     */
    public const CARRIAGE_RETURN = 'cr';
    /**
     * @var string
     */
    public const CARRIAGE_RETURN_LINE_FEED = 'crlf';
    /**
     * @var array<string, string>
     */
    private const ALLOWED_END_OF_LINE = [self::LINE_FEED => "\n", self::CARRIAGE_RETURN => "\r", self::CARRIAGE_RETURN_LINE_FEED => "\r\n"];
    /**
     * @see https://regex101.com/r/icaBBp/1
     * @var string
     */
    private const NEWLINE_REGEX = '#(?P<newLine>\\r\\n|\\n|\\r)#';
    /**
     * @see https://regex101.com/r/WrY9ZW/1/
     * @var string
     */
    private const VALID_NEWLINE_REGEX = '#^(?>\\r\\n|\\n|\\r)$#';
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
    public static function fromSingleCharacter(string $content) : self
    {
        $matches = \RectorPrefix20211231\Nette\Utils\Strings::match($content, self::VALID_NEWLINE_REGEX);
        if ($matches === null) {
            throw \Rector\FileFormatter\Exception\InvalidNewLineStringException::fromString($content);
        }
        return new self($content);
    }
    public static function fromContent(string $content) : self
    {
        $match = \RectorPrefix20211231\Nette\Utils\Strings::match($content, self::NEWLINE_REGEX);
        if (isset($match['newLine'])) {
            return self::fromSingleCharacter($match['newLine']);
        }
        return self::fromSingleCharacter(\PHP_EOL);
    }
    public static function fromEditorConfig(string $endOfLine) : self
    {
        if (!\array_key_exists($endOfLine, self::ALLOWED_END_OF_LINE)) {
            $allowedEndOfLineValues = \array_keys(self::ALLOWED_END_OF_LINE);
            $message = \sprintf('The endOfLine "%s" is not allowed. Allowed are "%s"', $endOfLine, \implode(',', $allowedEndOfLineValues));
            throw \Rector\FileFormatter\Exception\InvalidNewLineStringException::create($message);
        }
        return self::fromSingleCharacter(self::ALLOWED_END_OF_LINE[$endOfLine]);
    }
}
