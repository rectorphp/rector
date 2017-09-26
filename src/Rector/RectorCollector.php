<?php declare(strict_types=1);

namespace Rector\Rector;

use Nette\Utils\ObjectMixin;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\Rector\RectorNotFoundException;

final class RectorCollector
{
    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    public function addRector(RectorInterface $rector): void
    {
        $this->rectors[get_class($rector)] = $rector;
    }

    public function getRector(string $class): RectorInterface
    {
        $this->ensureRectorsIsFound($class);

        return $this->rectors[$class];
    }

    private function ensureRectorsIsFound(string $class): void
    {
        if (isset($this->rectors[$class])) {
            return;
        }

        $rectorClasses = array_keys($this->rectors);

        $suggestion = ObjectMixin::getSuggestion($rectorClasses, $class);
        $suggestionMessage = $suggestion ? sprintf(' Did you mean "%s"?', $suggestion) : '';

        $availableOptionsMessage = sprintf(
            ' Available rectors are: "%s".',
            implode('", "', $rectorClasses)
        );

        throw new RectorNotFoundException(sprintf(
            'Rectors class "%s" was not found.%s',
            $class,
            $suggestionMessage ?: $availableOptionsMessage
        ));
    }

    public function getRectorCount(): int
    {
        return count($this->rectors);
    }

    /**
     * @return RectorInterface[]
     */
    public function getRectors(): array
    {
        return $this->rectors;
    }
}
