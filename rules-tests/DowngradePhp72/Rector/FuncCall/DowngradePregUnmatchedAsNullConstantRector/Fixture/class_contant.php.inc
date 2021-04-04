<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class ClassConstant
{
    public const PREG_UNMATCHED_AS_NULL = \PREG_UNMATCHED_AS_NULL;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class ClassConstant
{
    public const PREG_UNMATCHED_AS_NULL = 512;
}

?>
