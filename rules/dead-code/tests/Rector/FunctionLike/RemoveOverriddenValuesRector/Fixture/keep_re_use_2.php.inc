<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

use Nette\Utils\Strings;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;

final class KeepReUse2
{
    public function resolveFirstTypes(MarkdownDifferAndFormatter $markdownDiffer, $old, $new)
    {
        $diff = $markdownDiffer->diff($old, $new);

        // remove first line, just meta info added by UnifiedDiffOutputBuilder
        $diff = Strings::replace($diff, '#^(.*\n){1}#');

        return $diff;
    }
}
