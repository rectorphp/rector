<?php

declare (strict_types=1);
namespace RectorPrefix202402\Symplify\EasyParallel\Contract;

use JsonSerializable;
/**
 * @api
 */
interface SerializableInterface extends JsonSerializable
{
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json) : self;
}
