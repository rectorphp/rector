<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare(strict_types=1);

namespace Nette\DI;

use Nette;

final class Definition
{
    /**
     * @return static
     * @deprecated
     */
    public function setClass(?string $type): self
    {
        if (func_num_args() > 1) {
            @trigger_error(__METHOD__ . '() second parameter $args is deprecated, use setFactory()', E_USER_DEPRECATED);
            if ($args = func_get_arg(1)) {
                $this->setFactory($type, $args);
            }
        }

        return $this;
    }

    public function setInject(bool $state = true): self
    {
        @trigger_error(__METHOD__ . "() is deprecated, use addTag('inject')", E_USER_DEPRECATED);

        return $this->addTag(InjectExtension::TAG_INJECT, $state);
    }
}
