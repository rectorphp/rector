<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPOffice\Set;

use RectorPrefix20220606\Rector\Set\Contract\SetListInterface;
final class PHPOfficeSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const PHPEXCEL_TO_PHPSPREADSHEET = __DIR__ . '/../../config/sets/phpexcel-to-phpspreadsheet.php';
}
