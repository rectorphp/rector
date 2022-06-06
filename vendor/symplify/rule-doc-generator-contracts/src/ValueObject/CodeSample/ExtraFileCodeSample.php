<?php

declare (strict_types=1);
namespace Symplify\RuleDocGenerator\ValueObject\CodeSample;

use Symplify\RuleDocGenerator\ValueObject\AbstractCodeSample;
final class ExtraFileCodeSample extends \Symplify\RuleDocGenerator\ValueObject\AbstractCodeSample
{
    /**
     * @var string
     */
    private $extraFile;
    public function __construct(string $badCode, string $goodCode, string $extraFile)
    {
        $this->extraFile = $extraFile;
        parent::__construct($badCode, $goodCode);
    }
    public function getExtraFile() : string
    {
        return $this->extraFile;
    }
}
