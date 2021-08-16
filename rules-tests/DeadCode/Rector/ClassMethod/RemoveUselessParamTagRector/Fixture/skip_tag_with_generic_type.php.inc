<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use Ds\Vector;
use IteratorAggregate;

/**
 * @template TValue
 *
 * @implements IteratorAggregate<int, TValue>
 */
final class ImmutableVector implements IteratorAggregate
{
	/**
	 * @var Vector<TValue>
	 */
	private Vector $vector;

	/**
	 * @param iterable<TValue>|null $values
	 */
	public function __construct(?iterable $values = null)
	{
		$this->vector = new Vector($values ?? []);
	}
}
?>

