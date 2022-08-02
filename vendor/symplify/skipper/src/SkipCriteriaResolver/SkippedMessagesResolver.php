<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Skipper\SkipCriteriaResolver;

use RectorPrefix202208\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202208\Symplify\Skipper\ValueObject\Option;
final class SkippedMessagesResolver
{
    /**
     * @var array<string, string[]|null>
     */
    private $skippedMessages = [];
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }
    /**
     * @return array<string, string[]|null>
     */
    public function resolve() : array
    {
        if ($this->skippedMessages !== []) {
            return $this->skippedMessages;
        }
        $skip = $this->parameterProvider->provideArrayParameter(Option::SKIP);
        foreach ($skip as $key => $value) {
            // e.g. [SomeClass::class] → shift values to [SomeClass::class => null]
            if (\is_int($key)) {
                $key = $value;
                $value = null;
            }
            if (!\is_string($key)) {
                continue;
            }
            if (\substr_count($key, ' ') === 0) {
                continue;
            }
            $this->skippedMessages[$key] = $value;
        }
        return $this->skippedMessages;
    }
}
