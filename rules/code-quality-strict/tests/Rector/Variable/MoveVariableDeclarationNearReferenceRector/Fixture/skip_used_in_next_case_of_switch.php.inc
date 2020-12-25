<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsedInNextCaseOfSwitch
{
    function myMethod()
    {
        $do_sfe = $do_trt->get_sfe_do();

         switch($do_trt->get_mar_do()->get_cod_s()) {
             case 'MAR1':
                 return class1::get_price_all($do_sfe);
             case 'MAR2':
                 return class1::get_price_all2($do_sfe);
         }
    }
}

?>
