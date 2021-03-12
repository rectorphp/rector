<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipIssue1093
{
    public function run($themes = [])
    {
        $theme_uninstallable = TRUE;
        if (1 == 2) {
            $theme_uninstallable = FALSE;
        }
        // Check it isn't the base of theme of an installed theme.
        foreach ([1, 2, 3] as $themename => $dependency) {
            if (!empty($themes[$themename]->status)) {
                $theme_uninstallable = FALSE;
            }
        }

        if ($theme_uninstallable) {
            return true;
        }

        return false;
    }
}
