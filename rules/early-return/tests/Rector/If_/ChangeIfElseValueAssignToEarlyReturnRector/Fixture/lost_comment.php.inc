<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector\Fixture;

class LostComment
{
    protected function replaceUrl($internalUrl, $publicUrl, $contenttext)
    {
        if (null === $publicUrl) {
            // parameters could not be resolved into a url
            $contenttext = preg_replace('@href=(["\'\s]*'.preg_quote($internalUrl, '@').'["\'\s]*)@', '', $contenttext);
        } else {
            // else comment
            $contenttext = str_replace($internalUrl, $publicUrl, $contenttext);
        }

        return $contenttext;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector\Fixture;

class LostComment
{
    protected function replaceUrl($internalUrl, $publicUrl, $contenttext)
    {
        if (null === $publicUrl) {
            // parameters could not be resolved into a url
            return preg_replace('@href=(["\'\s]*'.preg_quote($internalUrl, '@').'["\'\s]*)@', '', $contenttext);
        }

        // else comment
        return str_replace($internalUrl, $publicUrl, $contenttext);
    }
}

?>
