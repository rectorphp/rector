<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer\Whitespace;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class IndentCharacterDetector
{
    /**
     * @var string
     * @see https://regex101.com/r/w5E8Rh/1
     */
    private const FOUR_SPACE_START_REGEX = '#^ {4}#m';

    /**
     * Solves https://github.com/rectorphp/rector/issues/1964
     *
     * Some files have spaces, some have tabs. Keep the original indent if possible.
     *
     * @param Stmt[] $stmts
     */
    public function detect(array $stmts): string
    {
        foreach ($stmts as $stmt) {
            if (! $stmt instanceof Node) {
                continue;
            }

            $fileInfo = $stmt->getAttribute(AttributeKey::FILE_INFO);
            if (! $fileInfo instanceof SmartFileInfo) {
                continue;
            }

            $whitespaces = count(Strings::matchAll($fileInfo->getContents(), self::FOUR_SPACE_START_REGEX));
            $tabs = count(Strings::matchAll($fileInfo->getContents(), '#^\t#m'));

            // tab vs space
            return ($whitespaces <=> $tabs) >= 0 ? ' ' : "\t";
        }

        // use space by default
        return ' ';
    }
}
