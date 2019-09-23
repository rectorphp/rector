<?php

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

function break_missplaced_return()
{
    if (true == $objWebsite ) {
        $arrobjProperties = (array) $objWebsite->fetchProperties( );
        if (false == array_key_exists( $Id, $Properties)) {
            break;
        }
        $strBaseName = $strPortalSuffix . '/';

        return $strBaseName;
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

function break_missplaced_return()
{
    if (true == $objWebsite ) {
        $arrobjProperties = (array) $objWebsite->fetchProperties( );
        if (false == array_key_exists( $Id, $Properties)) {
            return;
        }
        $strBaseName = $strPortalSuffix . '/';

        return $strBaseName;
    }
}

?>
