<?php

namespace Doctrine\ORM\Mapping;

// mimics @see https://github.com/doctrine/orm/blob/master/lib/Doctrine/ORM/Annotation/HasLifecycleCallbacks.php

if (class_exists('Doctrine\ORM\Mapping\HasLifecycleCallbacks')) {
    return;
}

/**
 * @Annotation
 * @Target("CLASS")
 */
final class HasLifecycleCallbacks implements Annotation
{
}
