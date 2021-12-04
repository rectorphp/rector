<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Annotation;

use Rector\Core\Contract\Rector\RectorInterface;
final class RectorsChangelogResolver
{
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Annotation\AnnotationExtractor
     */
    private $annotationExtractor;
    public function __construct(\Rector\ChangesReporting\Annotation\AnnotationExtractor $annotationExtractor)
    {
        $this->annotationExtractor = $annotationExtractor;
    }
    /**
     * @param array<class-string<RectorInterface>> $rectorClasses
     * @return array<class-string, string>
     */
    public function resolve(array $rectorClasses) : array
    {
        $rectorClassesToChangelogUrls = $this->resolveIncludingMissing($rectorClasses);
        return \array_filter($rectorClassesToChangelogUrls);
    }
    /**
     * @param array<class-string<RectorInterface>> $rectorClasses
     * @return array<class-string, string|null>
     */
    public function resolveIncludingMissing(array $rectorClasses) : array
    {
        $rectorClassesToChangelogUrls = [];
        foreach ($rectorClasses as $rectorClass) {
            $changelogUrl = $this->annotationExtractor->extractAnnotationFromClass($rectorClass, '@changelog');
            $rectorClassesToChangelogUrls[$rectorClass] = $changelogUrl;
        }
        return $rectorClassesToChangelogUrls;
    }
}
