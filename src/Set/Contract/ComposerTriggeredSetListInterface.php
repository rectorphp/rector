<?php

declare (strict_types=1);
namespace Rector\Set\Contract;

/**
 * @api to be implemented by framework packages
 * @experimental 2024-06
 */
interface ComposerTriggeredSetListInterface extends \Rector\Set\Contract\SetListInterface
{
    /**
     * Key used in @see \Rector\Configuration\RectorConfigBuilder::withPreparedSets() method
     */
    public static function getName() : string;
    public static function getPackageName() : string;
    public static function getPackageVersion() : string;
}
