<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class NewToStaticCall
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $staticCallClass;

    /**
     * @var string
     */
    private $staticCallMethod;

    public function __construct(string $type, string $staticCallClass, string $staticCallMethod)
    {
        $this->type = $type;
        $this->staticCallClass = $staticCallClass;
        $this->staticCallMethod = $staticCallMethod;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStaticCallClass(): string
    {
        return $this->staticCallClass;
    }

    public function getStaticCallMethod(): string
    {
        return $this->staticCallMethod;
    }
}
