<?php declare(strict_types=1);

namespace Rector\ConsoleDiffer;

use SebastianBergmann\Diff\Differ;

final class MarkdownDifferAndFormatter
{
    /**
     * @var Differ
     */
    private $markdownDiffer;

    public function __construct(Differ $markdownDiffer)
    {
        $this->markdownDiffer = $markdownDiffer;
    }

    public function bareDiffAndFormatWithoutColors(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        $diff = $this->markdownDiffer->diff($old, $new);

        // remove first line, just meta info added by UnifiedDiffOutputBuilder
        $diff = preg_replace("/^(.*\n){1}/", '', $diff);

        return $this->removeTrailingWhitespaces($diff);
    }

    /**
     * Removes UnifiedDiffOutputBuilder generated pre-spaces " \n" => "\n"
     */
    private function removeTrailingWhitespaces(string $diff): string
    {
        $diff = preg_replace('#( ){1,}\n#', PHP_EOL, $diff);

        return rtrim($diff);
    }
}
