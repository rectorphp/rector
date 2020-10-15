<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;

/**
 * @see \Rector\Core\Tests\PhpParser\Printer\CommentRemover\CommentRemoverTest
 */
final class CommentRemover
{
    /**
     * @see https://regex101.com/r/mpKusH/1
     * @var string
     */
    private const START_COMMENT_REGEX = '#\/*\*(.*?)\*\/#s';

    /**
     * @see https://regex101.com/r/YOXUuD/1
     * @var string
     */
    private const START_GRID_COMMENT_REGEX = '#^(\s+)?\#(.*?)$#m';

    /**
     * @see https://regex101.com/r/zTLygY/3
     * @var string
     */
    private const START_DOUBLE_SLASH_COMMENT_REGEX = '#(\/\/.*?)[^;]$#m';

    public function remove(string $content): string
    {
        // remove /** ... */
        $content = Strings::replace($content, self::START_COMMENT_REGEX);

        // remove /* ... */
        $content = Strings::replace($content, self::START_COMMENT_REGEX);

        // remove # ...
        $content = Strings::replace($content, self::START_GRID_COMMENT_REGEX);

        // remove // ...
        return Strings::replace($content, self::START_DOUBLE_SLASH_COMMENT_REGEX);
    }
}
