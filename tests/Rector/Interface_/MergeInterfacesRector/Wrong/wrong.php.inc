<?php declare(strict_types=1);

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeInterface;
use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface, SomeOldInterface
{

}