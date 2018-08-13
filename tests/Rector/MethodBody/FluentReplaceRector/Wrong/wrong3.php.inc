<?php

namespace SomeNamespace;

class SomeClassWithReturnAnnotations
{
    /**
     * @return SomeClassWithReturnAnnotations
     */
    public function someFunction()
    {
        return $this;
    }
}
