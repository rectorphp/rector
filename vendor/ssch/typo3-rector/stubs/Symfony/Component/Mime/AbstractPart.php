<?php

namespace RectorPrefix20210615\Symfony\Component\Mime;

if (\class_exists('Symfony\\Component\\Mime\\AbstractPart')) {
    return;
}
abstract class AbstractPart
{
}
