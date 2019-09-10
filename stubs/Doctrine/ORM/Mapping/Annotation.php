<?php

namespace Doctrine\ORM\Mapping;

if (interface_exists('Doctrine\ORM\Mapping\Annotation')) {
    return;
}

interface Annotation
{
}
