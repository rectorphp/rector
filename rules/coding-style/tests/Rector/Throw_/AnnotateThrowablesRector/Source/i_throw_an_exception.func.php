<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond;

/**
 * @param null|string $switch
 * @throws TheException
 * @throws TheExceptionTheSecond
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheThird
 */
function i_throw_an_exception(?string $switch = null):bool
{
    switch ($switch) {
        case 'two':
            throw new TheExceptionTheSecond("I'm a function that throws an exception.");
        case 'third':
            throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheThird("I'm a function that throws an exception.");
        default:
            throw new TheException("I'm a function that throws an exception.");
    }
}
