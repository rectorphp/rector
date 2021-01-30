<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\ValueObject;

final class TagName
{
    /**
     * Use by Symfony to autowire dependencies outside constructor,
     * @see https://symfony.com/doc/current/service_container/autowiring.html#autowiring-other-methods-e-g-setters-and-public-typed-properties
     * @var string
     */
    public const REQUIRED = 'required';

    /**
     * @var string
     */
    public const API = 'api';

    /**
     * Nette @inject annotation mostly
     * @var string
     */
    public const INJECT = 'inject';
}
