<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Rector\Rector\AbstractChangeMethodNameRector;

final class ConfigurableChangeMethodNameRector extends AbstractChangeMethodNameRector
{
    /**
     * @var string[][]
     */
    private $perClassOldToNewMethod;

    /**
     * @param string[][] $perClassOldToNewMethod
     */
    public function setPerClassOldToNewMethods(array $perClassOldToNewMethod): void
    {
        $this->perClassOldToNewMethod = $perClassOldToNewMethod;
    }

    /**
     * @return string[][] { class => [ oldMethod => newMethod ] }
     */
    protected function getPerClassOldToNewMethods(): array
    {
        return $this->perClassOldToNewMethod;
    }
}
