<?php

declare (strict_types=1);
namespace Rector\PhpParser\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class PhpParserSetList implements SetListInterface
{
    /**
     * @api
     * @var string
     */
    public const PHP_PARSER_50 = __DIR__ . '/../../config/sets/php-parser-50.php';
}
