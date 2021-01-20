<?php

namespace Rector\Generic\ValueObject;

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

    /**
     * @return string
     */
    public function getListClass(): string
    {
        return $this->listClass;
    }

    /**
     * @return string
     */
    public function getItemClass(): string
    {
        return $this->itemClass;
    }

    /**
     * @return string
     */
    public function getAddMethod(): string
    {
        return $this->addMethod;
    }
}
