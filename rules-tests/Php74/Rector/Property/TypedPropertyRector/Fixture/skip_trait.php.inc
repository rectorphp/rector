<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

trait JsonBodyTrait
{
    private $body = null;

    public function setJsonBody( array $body ): self {
        $this->body = $body;
        return $this;
    }

    public function getJsonBody(): ?array {
        return $this->body;
    }
}

class UsingTraig
{
    use JsonBodyTrait;
}

