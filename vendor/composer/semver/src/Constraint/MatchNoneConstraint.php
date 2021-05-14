<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20210514\Composer\Semver\Constraint;

/**
 * Blackhole of constraints, nothing escapes it
 */
class MatchNoneConstraint implements \RectorPrefix20210514\Composer\Semver\Constraint\ConstraintInterface
{
    /** @var string|null */
    protected $prettyString;
    /**
     * @param ConstraintInterface $provider
     *
     * @return bool
     */
    public function matches(\RectorPrefix20210514\Composer\Semver\Constraint\ConstraintInterface $provider)
    {
        return \false;
    }
    public function compile($operator)
    {
        return 'false';
    }
    /**
     * @param string|null $prettyString
     */
    public function setPrettyString($prettyString)
    {
        $this->prettyString = $prettyString;
    }
    /**
     * @return string
     */
    public function getPrettyString()
    {
        if ($this->prettyString) {
            return $this->prettyString;
        }
        return (string) $this;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return '[]';
    }
    /**
     * {@inheritDoc}
     */
    public function getUpperBound()
    {
        return new \RectorPrefix20210514\Composer\Semver\Constraint\Bound('0.0.0.0-dev', \false);
    }
    /**
     * {@inheritDoc}
     */
    public function getLowerBound()
    {
        return new \RectorPrefix20210514\Composer\Semver\Constraint\Bound('0.0.0.0-dev', \false);
    }
}
