<?php

namespace RectorPrefix202604;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (\PHP_VERSION_ID < 80500) {
    // @author Daniel Scherzer <daniel.e.scherzer@gmail.com>
    #[\Attribute(\Attribute::TARGET_ALL)]
    final class DelayedTargetValidation
    {
    }
    // @author Daniel Scherzer <daniel.e.scherzer@gmail.com>
    \class_alias('RectorPrefix202604\DelayedTargetValidation', 'DelayedTargetValidation', \false);
}
