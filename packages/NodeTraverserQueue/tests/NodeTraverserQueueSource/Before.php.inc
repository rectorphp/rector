<?php

function someMethod()
{
    $someAnonymousClass = new class
        extends NodeVisitorAbstract
    {
        private $propertyFollowedByBlankLine;

        private $property;
    };
}
