<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source;

final class SelfButNewAssignVersion
{
    public $status;

    public function withStatus($status): self
    {
        $self = new $this;
        $self->status = $status;

        return $self;
    }
}
