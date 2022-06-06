<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Set;

use Rector\Set\Contract\SetListInterface;
final class PHPOfficeSetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const PHPEXCEL_TO_PHPSPREADSHEET = __DIR__ . '/../../config/sets/phpexcel-to-phpspreadsheet.php';
}
