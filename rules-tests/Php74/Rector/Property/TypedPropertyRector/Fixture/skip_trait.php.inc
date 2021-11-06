<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

trait SkipTrait
{
    private $body;

    public function setJsonBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getJsonBody(): ?array
    {
        return $this->body;
    }
}
