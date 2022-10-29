<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\ConstantMap;

final class SymfonyCommandConstantMap
{
    /**
     * @see https://github.com/symfony/symfony/blob/8e8207bb72d7f2cb8be355994ad2fcfa97c00f74/src/Symfony/Component/Console/Command/Command.php#L36-L38
     *
     * @var array<int, string>
     */
    public const RETURN_TO_CONST = [0 => 'SUCCESS', 1 => 'FAILURE', 2 => 'INVALID'];
}
