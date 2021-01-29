<?php declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class DimFetchAssignToMethodCall
{
    /**
     * @var string
     */
    private $listClass;

    /**
     * @var string
     */
    private $itemClass;

    /**
     * @var string
     */
    private $addMethod;

    public function __construct(string $listClass, string $itemClass, string $addMethod)
    {
        $this->listClass = $listClass;
        $this->itemClass = $itemClass;
        $this->addMethod = $addMethod;
    }

    public function getListClass(): string
    {
        return $this->listClass;
    }

    public function getItemClass(): string
    {
        return $this->itemClass;
    }

    public function getAddMethod(): string
    {
        return $this->addMethod;
    }
}
