<?php declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector\Source;

final class OldClassWithConstants
{
    /**
     * @var string
     */
    public const DEVELOPMENT =  'development';

    /**
     * @var string
     */
    public const PRODUCTION =  'production';
}
