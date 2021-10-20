<?php

namespace RectorPrefix20211020\TYPO3\CMS\SysNote\Domain\Repository;

if (\class_exists('TYPO3\\CMS\\SysNote\\Domain\\Repository\\SysNoteRepository')) {
    return;
}
class SysNoteRepository
{
    /**
     * @param int $author
     * @param int $position
     * @return mixed[]
     */
    public function findByPidsAndAuthorId($pids, $author, $position = null)
    {
        $author = (int) $author;
        $position = (int) $position;
        return [];
    }
}
