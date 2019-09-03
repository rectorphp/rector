<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Covariance;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

interface CommandInterface {
    public function getInput(): InputInterface;
}

class SomeCommand implements CommandInterface {
    public function getInput(): InputInterface
    {
        return new ArrayInput([]);
    }
}
