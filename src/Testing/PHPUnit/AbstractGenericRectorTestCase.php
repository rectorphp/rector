<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Rector\Exception\ShouldNotHappenException;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractGenericRectorTestCase extends AbstractKernelTestCase
{
    protected function getRectorClass(): string
    {
        // can be implemented
        return '';
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        // can be implemented, has the highest priority
        return [];
    }

    /**
     * @return mixed[]|null[]
     */
    protected function getCurrentTestRectorClasses(): array
    {
        if ($this->getRectorsWithConfiguration() !== []) {
            foreach (array_keys($this->getRectorsWithConfiguration()) as $rectorClass) {
                $this->ensureRectorClassIsValid($rectorClass, 'getRectorsWithConfiguration');
            }

            return $this->getRectorsWithConfiguration();
        }

        $rectorClass = $this->getRectorClass();
        $this->ensureRectorClassIsValid($rectorClass, 'getRectorClass');

        return [$rectorClass => null];
    }

    /**
     * Return interface type that extends @see \Rector\Contract\Rector\RectorInterface;
     */
    abstract protected function getRectorInterface(): string;

    private function ensureRectorClassIsValid(string $rectorClass, string $methodName): void
    {
        if (is_a($rectorClass, $this->getRectorInterface(), true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" in "%s()" method must be type of "%s"',
            $rectorClass,
            $methodName,
            $this->getRectorInterface()
        ));
    }
}
