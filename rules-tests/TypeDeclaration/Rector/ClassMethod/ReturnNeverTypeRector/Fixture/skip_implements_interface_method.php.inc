<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Source\RunnableInterface;

class SkipImplementsInterfaceMethod implements RunnableInterface
{
    public function run(): ?Node
    {
        throw new ShouldNotHappenException();
    }
}
