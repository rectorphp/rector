<?php declare(strict_types=1);

namespace Rector\ContributorTools\OutputFormatter;

use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Contract\Rector\RectorInterface;
use Rector\ContributorTools\Contract\OutputFormatterInterface;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    public function getName(): string
    {
        return 'json';
    }

    /**
     * @param RectorInterface[] $genericRectors
     * @param RectorInterface[] $packageRectors
     */
    public function format(array $genericRectors, array $packageRectors): void
    {
        /** @var RectorInterface[] $rectors */
        $rectors = array_merge($genericRectors, $packageRectors);

        $rectorData = [];
        foreach ($rectors as $rector) {
            $rectorConfiguration = $rector->getDefinition();

            $rectorData[] = [
                'class' => get_class($rector),
                'tags' => $this->createTagsFromClass(get_class($rector)),
                'description' => $rectorConfiguration->getDescription(),
                'code_samples' => $this->prepareCodeSamples($rectorConfiguration),
                'is_configurable' => $this->resolveIsConfigurable($rectorConfiguration),
            ];
        }

        $data = [
            'rectors' => $rectorData,
            'rector_total_count' => count($rectors),
            'generated_at' => (string) DateTime::from('now'),
        ];

        echo Json::encode($data, Json::PRETTY);
    }

    /**
     * @return string[]
     */
    private function createTagsFromClass(string $rectorClass): array
    {
        $tags = [];
        $rectorClassParts = explode('\\', $rectorClass);

        foreach ($rectorClassParts as $rectorClassPart) {
            if ($rectorClassPart === 'Rector') {
                continue;
            }

            foreach (Strings::split($rectorClassPart, '#(?=[A-Z])#') as $part) {
                if (in_array($part, ['Rector', 'To', '', 'Is', 'Like'], true)) {
                    continue;
                }

                $part = rtrim($part, '_');
                $tags[] = strtolower($part);
            }
        }

        return array_values(array_unique($tags));
    }

    /**
     * @return mixed[]
     */
    private function prepareCodeSamples(RectorDefinition $rectorDefinition): array
    {
        $codeSamplesData = [];
        foreach ($rectorDefinition->getCodeSamples() as $codeSample) {
            $codeSampleData = [
                'before' => $codeSample->getCodeBefore(),
                'after' => $codeSample->getCodeAfter(),
            ];

            if ($codeSample instanceof ConfiguredCodeSample) {
                $codeSampleData['configuration'] = $codeSample->getConfiguration();
            }

            $codeSamplesData[] = $codeSampleData;
        }

        return $codeSamplesData;
    }

    private function resolveIsConfigurable(RectorDefinition $rectorDefinition): bool
    {
        foreach ($rectorDefinition->getCodeSamples() as $codeSample) {
            if ($codeSample instanceof ConfiguredCodeSample) {
                return true;
            }
        }

        return false;
    }
}
