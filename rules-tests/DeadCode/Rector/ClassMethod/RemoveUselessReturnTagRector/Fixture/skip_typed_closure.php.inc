<?php

declare(strict_types = 1);

interface X {
	/**
	 * @return \Closure(int): bool
	 */
	function createFilter(): \Closure;
}
?>
