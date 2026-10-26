<?php

declare (strict_types=1);
namespace Rector\Visibility\ValueObject;

use Rector\Validation\RectorAssert;
use Rector\ValueObject\Visibility;
use RectorPrefix202610\Webmozart\Assert\Assert;
final class ChangeMethodVisibility
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    /**
     * @var Visibility::*
     * @readonly
     */
    private int $visibility;
    /**
     * @param Visibility::* $visibility
     */
    public function __construct(string $class, string $method, int $visibility)
    {
        $this->class = $class;
        $this->method = $method;
        $this->visibility = $visibility;
        RectorAssert::className($class);
        Assert::oneOf($visibility, [Visibility::PUBLIC, Visibility::PROTECTED, Visibility::PRIVATE, Visibility::STATIC, Visibility::ABSTRACT, Visibility::FINAL]);
    }
    public function getClass(): string
    {
        return $this->class;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    /**
     * @return Visibility::*
     */
    public function getVisibility(): int
    {
        return $this->visibility;
    }
}
