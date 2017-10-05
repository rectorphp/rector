<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Rector\Rector\AbstractClassReplacerRector;

final class ClassReplacerRector extends AbstractClassReplacerRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(array $oldToNewClasses)
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }

    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return $this->oldToNewClasses;
    }
}
