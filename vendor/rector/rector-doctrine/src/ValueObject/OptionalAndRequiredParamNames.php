<?php

declare (strict_types=1);
namespace Rector\Doctrine\ValueObject;

final class OptionalAndRequiredParamNames
{
    /**
     * @var mixed[]
     */
    private $optionalParamNames;
    /**
     * @var mixed[]
     */
    private $requiredParamNames;
    /**
     * @param string[] $optionalParamNames
     * @param string[] $requiredParamNames
     */
    public function __construct(array $optionalParamNames, array $requiredParamNames)
    {
        $this->optionalParamNames = $optionalParamNames;
        $this->requiredParamNames = $requiredParamNames;
    }
    /**
     * @return string[]
     */
    public function getOptionalParamNames() : array
    {
        return $this->optionalParamNames;
    }
    /**
     * @return string[]
     */
    public function getRequiredParamNames() : array
    {
        return $this->requiredParamNames;
    }
}
