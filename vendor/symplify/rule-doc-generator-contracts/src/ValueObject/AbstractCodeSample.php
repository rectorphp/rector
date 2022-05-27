<?php

declare (strict_types=1);
namespace Symplify\RuleDocGenerator\ValueObject;

use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;
use Symplify\RuleDocGenerator\Exception\ShouldNotHappenException;
abstract class AbstractCodeSample implements \Symplify\RuleDocGenerator\Contract\CodeSampleInterface
{
    /**
     * @var non-empty-string
     */
    private $goodCode;
    /**
     * @var non-empty-string
     */
    private $badCode;
    public function __construct(string $badCode, string $goodCode)
    {
        $badCode = \trim($badCode);
        $goodCode = \trim($goodCode);
        if ($badCode === '') {
            throw new \Symplify\RuleDocGenerator\Exception\ShouldNotHappenException('Bad sample good code cannot be empty');
        }
        if ($goodCode === '') {
            throw new \Symplify\RuleDocGenerator\Exception\ShouldNotHappenException('Good sample good code cannot be empty');
        }
        if ($goodCode === $badCode) {
            $errorMessage = \sprintf('Good and bad code cannot be identical: "%s"', $goodCode);
            throw new \Symplify\RuleDocGenerator\Exception\ShouldNotHappenException($errorMessage);
        }
        $this->goodCode = $goodCode;
        $this->badCode = $badCode;
    }
    public function getGoodCode() : string
    {
        return $this->goodCode;
    }
    public function getBadCode() : string
    {
        return $this->badCode;
    }
}
