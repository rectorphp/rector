<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220209\Tracy;

/**
 * Custom output for Debugger.
 */
interface IBarPanel
{
    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    function getTab();
    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    function getPanel();
}
