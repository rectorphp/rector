<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Covariance;

interface UserInterface {
    public function getName(): ?string;
}

class User implements UserInterface {
    public function getName(): ?string
    {
        return null;
    }
}
