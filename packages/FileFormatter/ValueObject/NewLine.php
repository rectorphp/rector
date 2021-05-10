<?php

declare(strict_types=1);

namespace Rector\FileFormatter\ValueObject;

use const PHP_EOL;
use Rector\FileFormatter\Exception\InvalidNewLineStringException;

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
    private const ALLOWED_END_OF_LINE = [
        self::LINE_FEED => "\n",
        self::CARRIAGE_RETURN => "\r",
        self::CARRIAGE_RETURN_LINE_FEED => "\r\n",
    ];

    private function __construct(
        private string $string
    ) {
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public static function fromSingleCharacter(string $string): self
    {
        $validNewLineRegularExpression = '/^(?>\r\n|\n|\r)$/';
        $validNewLine = preg_match($validNewLineRegularExpression, $string);

        if ($validNewLine !== 1) {
            throw InvalidNewLineStringException::fromString($string);
        }

        return new self($string);
    }

    public static function fromContent(string $string): self
    {
        $validNewLineRegularExpression = '/(?P<newLine>\r\n|\n|\r)/';
        $validNewLine = preg_match($validNewLineRegularExpression, $string, $match);
        if ($validNewLine === 1) {
            return self::fromSingleCharacter($match['newLine']);
        }

        return self::fromSingleCharacter(PHP_EOL);
    }

    public static function fromEditorConfig(string $endOfLine): self
    {
        if (! array_key_exists($endOfLine, self::ALLOWED_END_OF_LINE)) {
            $allowedEndOfLineValues = array_keys(self::ALLOWED_END_OF_LINE);
            $message = sprintf(
                'The endOfLine "%s" is not allowed. Allowed are "%s"',
                $endOfLine,
                implode(',', $allowedEndOfLineValues)
            );
            throw InvalidNewLineStringException::create($message);
        }

        return self::fromSingleCharacter(self::ALLOWED_END_OF_LINE[$endOfLine]);
    }
}
