<?php

declare (strict_types=1);
namespace RectorPrefix20220124\Symplify\EasyParallel\Contract;

use JsonSerializable;
interface SerializableInterface extends \JsonSerializable
{
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json) : self;
}
