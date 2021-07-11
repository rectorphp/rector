<?php

namespace RectorPrefix20210711\TYPO3\CMS\Backend\Backend\Shortcut;

if (\class_exists('TYPO3\\CMS\\Backend\\Backend\\Shortcut\\ShortcutRepository')) {
    return;
}
class ShortcutRepository
{
    /**
     * @param string $url
     * @return bool
     */
    public function shortcutExists($url)
    {
        $url = (string) $url;
        return \true;
    }
}
