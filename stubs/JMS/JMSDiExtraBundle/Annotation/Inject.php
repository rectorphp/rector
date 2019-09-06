<?php declare(strict_types=1);

namespace JMS\DiExtraBundle\Annotation;

if (class_exists('JMS\DiExtraBundle\Annotation\Inject')) {
    return;
}

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Inject extends Reference
{
}
