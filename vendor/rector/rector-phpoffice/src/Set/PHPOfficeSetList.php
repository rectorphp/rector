<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class PHPOfficeSetList implements SetListInterface
{
    /**
     * @api
     * @var string
     */
    public const PHPEXCEL_TO_PHPSPREADSHEET = __DIR__ . '/../../config/sets/phpexcel-to-phpspreadsheet.php';
}
