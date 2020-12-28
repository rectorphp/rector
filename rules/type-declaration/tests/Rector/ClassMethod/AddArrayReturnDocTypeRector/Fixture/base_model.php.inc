<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class BaseModel {}

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class DemoFile
{
	/**
	 * @template T of BaseModel
	 * @param class-string<T> $otherModelClass
	 * @return T[]
	 */
	public static function findMany(
		string $otherModelClass
	): array {
        /** @var T[] $rArray */
		$rArray = []; // get data from somewhere
		return $rArray;
    }
}
?>
