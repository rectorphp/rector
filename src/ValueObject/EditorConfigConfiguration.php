<?php
declare(strict_types=1);

namespace Rector\Core\ValueObject;

use Rector\Core\Exception\EditorConfigConfigurationException;
use Symplify\PackageBuilder\Configuration\StaticEolConfiguration;

final class EditorConfigConfiguration
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
     * @var string
     */
    public const TAB = 'tab';

    /**
     * @var string
     */
    public const SPACE = 'space';

    /**
     * @var array<string, string>
     */
    private const ALLOWED_END_OF_LINE = [
        self::LINE_FEED => "\n",
        self::CARRIAGE_RETURN => "\r",
        self::CARRIAGE_RETURN_LINE_FEED => "\r\n",
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_INDENT_STYLE = [self::TAB, self::SPACE];

    /**
     * @var string
     */
    private $indentStyle;

    /**
     * @var int
     */
    private $indentSize;

    /**
     * @var string
     */
    private $endOfLine;

    /**
     * @var int
     */
    private $tabWidth;

    /**
     * @var bool
     */
    private $insertFinalNewline;

    public function __construct(
        string $indentStyle,
        int $indentSize,
        string $endOfLine,
        int $tabWidth,
        bool $insertFinalNewline
    ) {
        if (! array_key_exists($endOfLine, self::ALLOWED_END_OF_LINE)) {
            $allowedEndOfLineValues = array_keys(self::ALLOWED_END_OF_LINE);
            throw new EditorConfigConfigurationException(sprintf(
                'The endOfLine "%s" is not allowed. Allowed are "%s"',
                $endOfLine,
                implode(',', $allowedEndOfLineValues)
            ));
        }

        if (! in_array($indentStyle, self::ALLOWED_INDENT_STYLE, true)) {
            throw new EditorConfigConfigurationException(sprintf(
                'The indentStyle "%s" is not allowed. Allowed are "%s"',
                $endOfLine,
                implode(',', self::ALLOWED_INDENT_STYLE)
            ));
        }

        $this->indentStyle = $indentStyle;
        $this->indentSize = $indentSize;
        $this->endOfLine = self::ALLOWED_END_OF_LINE[$endOfLine];
        $this->tabWidth = $tabWidth;
        $this->insertFinalNewline = $insertFinalNewline;
    }

    public function getIndentStyle(): string
    {
        return $this->indentStyle;
    }

    public function getIndentSize(): int
    {
        return $this->indentSize;
    }

    public function getEndOfLine(): string
    {
        return $this->endOfLine;
    }

    public function getFinalNewline(): string
    {
        return $this->insertFinalNewline ? StaticEolConfiguration::getEolChar() : '';
    }

    public function getIndent(): string
    {
        $isTab = $this->isTab();

        $indentSize = $isTab ? $this->tabWidth : $this->indentSize;
        $indentChar = $isTab ? "\t" : ' ';

        return str_pad('', $indentSize, $indentChar);
    }

    private function isTab(): bool
    {
        return $this->indentStyle === self::TAB;
    }
}
