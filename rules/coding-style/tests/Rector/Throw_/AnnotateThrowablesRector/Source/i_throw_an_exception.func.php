<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond;


/**
 * @param null|string $switch
 * @throws TheException
 * @throws TheExceptionTheSecond
 */
function i_throw_an_exception(?string $switch = null):bool
{
    if (null === $switch) {
        throw new TheExceptionTheSecond("I'm a function that throws an exception.");
    }

    throw new TheException("I'm a function that throws an exception.");
}
