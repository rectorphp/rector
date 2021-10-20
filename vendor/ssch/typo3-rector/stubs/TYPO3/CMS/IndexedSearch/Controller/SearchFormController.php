<?php

namespace RectorPrefix20211020\TYPO3\CMS\IndexedSearch\Controller;

if (\class_exists('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController')) {
    return;
}
class SearchFormController
{
    const WILDCARD_LEFT = 'foo';
    const WILDCARD_RIGHT = 'foo';
    public function pi_list_browseresults($showResultCount = \true, $addString = '', $addPart = '', $freeIndexUid = -1)
    {
        return '';
    }
    /**
     * @return void
     */
    protected function renderPagination()
    {
    }
}
