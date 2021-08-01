<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source;

trait SelfButCloneAssignVersionTrait
{
    public $status;

    public function withStatus($status): self
    {
        $self = clone $this;
        $self->status = $status;

        return $self;
    }
}
