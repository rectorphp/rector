<?php

namespace RectorPrefix20211029\Symfony\Component\Mime;

if (\class_exists('Symfony\\Component\\Mime\\AbstractPart')) {
    return;
}
abstract class AbstractPart
{
}
