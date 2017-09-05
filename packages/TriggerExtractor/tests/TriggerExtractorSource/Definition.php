<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\DI;

use Nette;

final class ServiceDefinition
{
    /**
     * @param  string|null
     * @return static
     * @deprecated
     */
    public function setClass($type)
    {
        ($this->notifier)();
        $this->type = $type;
        if (func_num_args() > 1) {
            trigger_error(__METHOD__ . '() second parameter $args is deprecated, use setFactory()', E_USER_DEPRECATED);
            if ($args = func_get_arg(1)) {
                $this->setFactory($type, $args);
            }
        }
        return $this;
    }

    /** @deprecated */
    public function setInject(bool $state = true)
    {
        trigger_error(__METHOD__ . "() is deprecated, use addTag('inject')", E_USER_DEPRECATED);
        return $this->addTag(Extensions\InjectExtension::TAG_INJECT, $state);
    }
}
