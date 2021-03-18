<?php

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\DiscriminatorMap')) {
    return;
}

class DiscriminatorMap implements Annotation
{
}
