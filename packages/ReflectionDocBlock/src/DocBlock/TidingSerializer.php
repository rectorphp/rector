<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use PhpCsFixer\DocBlock\DocBlock as PhpCsFixerDocBlock;
use PhpCsFixer\DocBlock\Line;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;

/**
 * As extra, it removes empty lines and spaces.
 */
final class TidingSerializer
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getDocComment(DocBlock $docBlock): string
    {
        $docComment = $this->serializer->getDocComment($docBlock);
        if ($this->isDocContentEmpty($docComment)) {
            return '';
        }

        $docComment = $this->clearTrailingSpace($docComment);
        $docComment = $this->clearEmptySpacesFromStartAndEnd($docComment);
        $docComment = $this->clearUnnededPreslashes($docComment);

        return $docComment;
    }

    /**
     * Inspiration https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/b23b5e0a4877a1c97d58f260ea0eb66fbff30e04/Symfony/CS/Fixer/Symfony/NoEmptyPhpdocFixer.php#L35
     */
    private function isDocContentEmpty(string $docContent): bool
    {
        return (bool) preg_match('#^/\*\*[\s\*]*\*/$#', $docContent);
    }

    private function clearTrailingSpace(string $content): string
    {
        preg_replace('/[ \t]+$/m', '', $content);

        return $content;
    }

    /**
     * Inspiration https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.7/src/Fixer/Phpdoc/PhpdocTrimFixer.php
     *
     * Consider reflection to keep API compatible.
     */
    private function clearEmptySpacesFromStartAndEnd(string $content): string
    {
        $content = $this->fixStart($content);
        // we need re-parse the docblock after fixing the start before
        // fixing the end in order for the lines to be correctly indexed
        $content = $this->fixEnd($content);

        return $content;
    }

    /**
     * Make sure the first useful line starts immediately after the first line.
     */
    private function fixStart(string $content): string
    {
        $doc = new PhpCsFixerDocBlock($content);

        /** @var Line[] $lines */
        $lines = $doc->getLines();
        $total = count($lines);
        foreach ($lines as $index => $line) {
            if (! $line->isTheStart()) {
                // don't remove lines with content and don't entirely delete docblocks
                if ($total - $index < 3 || $line->containsUsefulContent()) {
                    break;
                }

                $line->remove();
            }
        }

        return $doc->getContent();
    }

    /**
     * Make sure the last useful is immediately before after the final line.
     */
    private function fixEnd(string $content): string
    {
        $doc = new PhpCsFixerDocBlock($content);

        /** @var Line[] $lines */
        $lines = array_reverse($doc->getLines());
        $total = count($lines);
        foreach ($lines as $index => $line) {
            if (! $line->isTheEnd()) {
                // don't remove lines with content and don't entirely delete docblocks
                if ($total - $index < 3 || $line->containsUsefulContent()) {
                    break;
                }

                $line->remove();
            }
        }

        return $doc->getContent();
    }

    /**
     * phpDocumentor adds extra preslashes,
     * maybe could be resolved with custom @see DocBlock\Tags\Formatter instead of @see PassthroughFormatter
     */
    private function clearUnnededPreslashes(string $content): string
    {
        $content = str_replace('@var \\', '@var ', $content);

        return str_replace('@param \\', '@param ', $content);
    }
}
