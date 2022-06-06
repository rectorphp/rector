<?php

use My\Namespace\UnknownClass;

final class SkipUnknownClassInVar
{
    private ?\My\Namespace\UnknownClass $otherClass = null;

    public function getOtherClass(): ?\My\Namespace\UnknownClass
    {
        return $this->otherClass;
    }

    public function setOtherClass($otherClass): void
    {
        /** @var UnknownClass $otherClass */
        $this->otherClass = $otherClass;
    }
}
