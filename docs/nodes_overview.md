# Node Overview

* [Expressions](#expressions)
* [Children of "PhpParser\Node\Expr\AssignOp"](#children-of-phpparser-node-expr-assignop)
* [Children of "PhpParser\Node\Expr\BinaryOp"](#children-of-phpparser-node-expr-binaryop)
* [Children of "PhpParser\Node\Expr\Cast"](#children-of-phpparser-node-expr-cast)
* [Children of "PhpParser\Node\Name"](#children-of-phpparser-node-name)
* [Scalar nodes](#scalar-nodes)
* [Statements](#statements)
* [Various](#various)

## Expressions

### `PhpParser\Node\Expr\ArrayDimFetch`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable[0]
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$dim` - `/** @var null|Expr Array index / dim */`
<br>

### `PhpParser\Node\Expr\ArrayItem`

 * requires arguments on construct


#### Example PHP Code

```php
'name' => $Tom
```

#### Public Properties

 * `$key` - `/** @var null|Expr Key */`
 * `$value` - `/** @var Expr Value */`
 * `$byRef` - `/** @var bool Whether to assign by reference */`
 * `$unpack` - `/** @var bool Whether to unpack the argument */`
<br>

### `PhpParser\Node\Expr\Array_`


#### Example PHP Code

```php
[]
```

#### Public Properties

 * `$items` - `/** @var ArrayItem[] Items */`
<br>

### `PhpParser\Node\Expr\ArrowFunction`


#### Example PHP Code

```php
fn() => 1
```

#### Public Properties

 * `$static` - `/** @var bool */`
 * `$byRef` - `/** @var bool */`
 * `$params` - `/** @var Node\Param[] */`
 * `$returnType` - `/** @var null|Node\Identifier|Node\Name|Node\NullableType|Node\UnionType */`
 * `$expr` - `/** @var Expr */`
<br>

### `PhpParser\Node\Expr\Assign`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable = 'some value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignRef`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable =& $someOtherVariable
```

#### Public Properties

 * `$var` - `/** @var Expr Variable reference is assigned to */`
 * `$expr` - `/** @var Expr Variable which is referenced */`
<br>

### `PhpParser\Node\Expr\BitwiseNot`

 * requires arguments on construct


#### Example PHP Code

```php
~$someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\BooleanNot`

 * requires arguments on construct


#### Example PHP Code

```php
!true
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\ClassConstFetch`

 * requires arguments on construct


#### Example PHP Code

```php
SomeClass::SOME_CONSTANT
```

#### Public Properties

 * `$class` - `/** @var Name|Expr Class name */`
 * `$name` - `/** @var Identifier|Error Constant name */`
<br>

### `PhpParser\Node\Expr\Clone_`

 * requires arguments on construct


#### Example PHP Code

```php
clone $someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Closure`


#### Example PHP Code

```php
function () {
}
```

#### Public Properties

 * `$static` - `/** @var bool Whether the closure is static */`
 * `$byRef` - `/** @var bool Whether to return by reference */`
 * `$params` - `/** @var Node\Param[] Parameters */`
 * `$uses` - `/** @var ClosureUse[] use()s */`
 * `$returnType` - `/** @var null|Node\Identifier|Node\Name|Node\NullableType|Node\UnionType Return type */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Expr\ClosureUse`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable
```

#### Public Properties

 * `$var` - `/** @var Expr\Variable Variable to use */`
 * `$byRef` - `/** @var bool Whether to use by reference */`
<br>

### `PhpParser\Node\Expr\ConstFetch`

 * requires arguments on construct


#### Example PHP Code

```php
true
```

#### Public Properties

 * `$name` - `/** @var Name Constant name */`
<br>

### `PhpParser\Node\Expr\Empty_`

 * requires arguments on construct


#### Example PHP Code

```php
empty($someVariable)
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\ErrorSuppress`

 * requires arguments on construct


#### Example PHP Code

```php
@$someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Eval_`

 * requires arguments on construct


#### Example PHP Code

```php
eval('Some php code')
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Exit_`


#### Example PHP Code

```php
die
```

#### Public Properties

 * `$expr` - `/** @var null|Expr Expression */`
<br>

### `PhpParser\Node\Expr\FuncCall`

 * requires arguments on construct


#### Example PHP Code

```php
functionCall()
```

#### Public Properties

 * `$name` - `/** @var Node\Name|Expr Function name */`
 * `$args` - `/** @var Node\Arg[] Arguments */`
<br>

### `PhpParser\Node\Expr\Include_`

 * requires arguments on construct


#### Example PHP Code

```php
include $someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
 * `$type` - `/** @var int Type of include */`
<br>

### `PhpParser\Node\Expr\Instanceof_`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable instanceof SomeClass
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
 * `$class` - `/** @var Name|Expr Class name */`
<br>

### `PhpParser\Node\Expr\Isset_`

 * requires arguments on construct


#### Example PHP Code

```php
isset($variable)
```

#### Public Properties

 * `$vars` - `/** @var Expr[] Variables */`
<br>

### `PhpParser\Node\Expr\List_`

 * requires arguments on construct


#### Example PHP Code

```php
list($someVariable)
```

#### Public Properties

 * `$items` - `/** @var (ArrayItem|null)[] List of items to assign to */`
<br>

### `PhpParser\Node\Expr\MethodCall`

 * requires arguments on construct


#### Example PHP Code

```php
$someObject->methodName()
```

#### Public Properties

 * `$var` - `/** @var Expr Variable holding object */`
 * `$name` - `/** @var Identifier|Expr Method name */`
 * `$args` - `/** @var Arg[] Arguments */`
<br>

### `PhpParser\Node\Expr\New_`

 * requires arguments on construct


#### Example PHP Code

```php
new class
{
}
```

#### Public Properties

 * `$class` - `/** @var Node\Name|Expr|Node\Stmt\Class_ Class name */`
 * `$args` - `/** @var Node\Arg[] Arguments */`
<br>

### `PhpParser\Node\Expr\PostDec`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable--
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
<br>

### `PhpParser\Node\Expr\PostInc`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable++
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
<br>

### `PhpParser\Node\Expr\PreDec`

 * requires arguments on construct


#### Example PHP Code

```php
--$someVariable
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
<br>

### `PhpParser\Node\Expr\PreInc`

 * requires arguments on construct


#### Example PHP Code

```php
++$someVariable
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
<br>

### `PhpParser\Node\Expr\Print_`

 * requires arguments on construct


#### Example PHP Code

```php
print $someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\PropertyFetch`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable->propertyName
```

#### Public Properties

 * `$var` - `/** @var Expr Variable holding object */`
 * `$name` - `/** @var Identifier|Expr Property name */`
<br>

### `PhpParser\Node\Expr\ShellExec`

 * requires arguments on construct


#### Example PHP Code

```php
`encapsedstring`
```

#### Public Properties

 * `$parts` - `/** @var array Encapsed string array */`
<br>

### `PhpParser\Node\Expr\StaticCall`

 * requires arguments on construct


#### Example PHP Code

```php
SomeClass::methodName()
```

#### Public Properties

 * `$class` - `/** @var Node\Name|Expr Class name */`
 * `$name` - `/** @var Identifier|Expr Method name */`
 * `$args` - `/** @var Node\Arg[] Arguments */`
<br>

### `PhpParser\Node\Expr\StaticPropertyFetch`

 * requires arguments on construct


#### Example PHP Code

```php
SomeClass::$someProperty
```

#### Public Properties

 * `$class` - `/** @var Name|Expr Class name */`
 * `$name` - `/** @var VarLikeIdentifier|Expr Property name */`
<br>

### `PhpParser\Node\Expr\Ternary`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable ? true : false
```

#### Public Properties

 * `$cond` - `/** @var Expr Condition */`
 * `$if` - `/** @var null|Expr Expression for true */`
 * `$else` - `/** @var Expr Expression for false */`
<br>

### `PhpParser\Node\Expr\UnaryMinus`

 * requires arguments on construct


#### Example PHP Code

```php
-$someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\UnaryPlus`

 * requires arguments on construct


#### Example PHP Code

```php
+$someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Variable`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable
```

#### Public Properties

 * `$name` - `/** @var string|Expr Name */`
<br>

### `PhpParser\Node\Expr\YieldFrom`

 * requires arguments on construct


#### Example PHP Code

```php
yield from $someVariable
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression to yield from */`
<br>

### `PhpParser\Node\Expr\Yield_`


#### Example PHP Code

```php
yield
```

#### Public Properties

 * `$key` - `/** @var null|Expr Key expression */`
 * `$value` - `/** @var null|Expr Value expression */`
<br>

## Children of "PhpParser\Node\Expr\AssignOp"

### `PhpParser\Node\Expr\AssignOp\BitwiseAnd`

 * requires arguments on construct


#### Example PHP Code

```php
$variable &= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\BitwiseOr`

 * requires arguments on construct


#### Example PHP Code

```php
$variable |= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\BitwiseXor`

 * requires arguments on construct


#### Example PHP Code

```php
$variable ^= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Coalesce`

 * requires arguments on construct


#### Example PHP Code

```php
$variable ??= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Concat`

 * requires arguments on construct


#### Example PHP Code

```php
$variable .= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Div`

 * requires arguments on construct


#### Example PHP Code

```php
$variable /= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Minus`

 * requires arguments on construct


#### Example PHP Code

```php
$variable -= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Mod`

 * requires arguments on construct


#### Example PHP Code

```php
$variable %= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Mul`

 * requires arguments on construct


#### Example PHP Code

```php
$variable *= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Plus`

 * requires arguments on construct


#### Example PHP Code

```php
$variable += 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\Pow`

 * requires arguments on construct


#### Example PHP Code

```php
$variable **= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\ShiftLeft`

 * requires arguments on construct


#### Example PHP Code

```php
$variable <<= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\AssignOp\ShiftRight`

 * requires arguments on construct


#### Example PHP Code

```php
$variable >>= 'value'
```

#### Public Properties

 * `$var` - `/** @var Expr Variable */`
 * `$expr` - `/** @var Expr Expression */`
<br>

## Children of "PhpParser\Node\Expr\BinaryOp"

### `PhpParser\Node\Expr\BinaryOp\BitwiseAnd`

 * requires arguments on construct


#### Example PHP Code

```php
1 & 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\BitwiseOr`

 * requires arguments on construct


#### Example PHP Code

```php
1 | 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\BitwiseXor`

 * requires arguments on construct


#### Example PHP Code

```php
1 ^ 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\BooleanAnd`

 * requires arguments on construct


#### Example PHP Code

```php
1 && 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\BooleanOr`

 * requires arguments on construct


#### Example PHP Code

```php
1 || 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Coalesce`

 * requires arguments on construct


#### Example PHP Code

```php
1 ?? 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Concat`

 * requires arguments on construct


#### Example PHP Code

```php
1 . 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Div`

 * requires arguments on construct


#### Example PHP Code

```php
1 / 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Equal`

 * requires arguments on construct


#### Example PHP Code

```php
1 == 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Greater`

 * requires arguments on construct


#### Example PHP Code

```php
1 > 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\GreaterOrEqual`

 * requires arguments on construct


#### Example PHP Code

```php
1 >= 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Identical`

 * requires arguments on construct


#### Example PHP Code

```php
1 === 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\LogicalAnd`

 * requires arguments on construct


#### Example PHP Code

```php
1 and 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\LogicalOr`

 * requires arguments on construct


#### Example PHP Code

```php
1 or 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\LogicalXor`

 * requires arguments on construct


#### Example PHP Code

```php
1 xor 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Minus`

 * requires arguments on construct


#### Example PHP Code

```php
1 - 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Mod`

 * requires arguments on construct


#### Example PHP Code

```php
1 % 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Mul`

 * requires arguments on construct


#### Example PHP Code

```php
1 * 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\NotEqual`

 * requires arguments on construct


#### Example PHP Code

```php
1 != 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\NotIdentical`

 * requires arguments on construct


#### Example PHP Code

```php
1 !== 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Plus`

 * requires arguments on construct


#### Example PHP Code

```php
1 + 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Pow`

 * requires arguments on construct


#### Example PHP Code

```php
1 ** 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\ShiftLeft`

 * requires arguments on construct


#### Example PHP Code

```php
1 << 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\ShiftRight`

 * requires arguments on construct


#### Example PHP Code

```php
1 >> 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Smaller`

 * requires arguments on construct


#### Example PHP Code

```php
1 < 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\SmallerOrEqual`

 * requires arguments on construct


#### Example PHP Code

```php
1 <= 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

### `PhpParser\Node\Expr\BinaryOp\Spaceship`

 * requires arguments on construct


#### Example PHP Code

```php
1 <=> 'a'
```

#### Public Properties

 * `$left` - `/** @var Expr The left hand side expression */`
 * `$right` - `/** @var Expr The right hand side expression */`
<br>

## Children of "PhpParser\Node\Expr\Cast"

### `PhpParser\Node\Expr\Cast\Array_`

 * requires arguments on construct


#### Example PHP Code

```php
(array) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Cast\Bool_`

 * requires arguments on construct


#### Example PHP Code

```php
(bool) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Cast\Double`

 * requires arguments on construct


#### Example PHP Code

```php
(double) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Cast\Int_`

 * requires arguments on construct


#### Example PHP Code

```php
(int) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Cast\Object_`

 * requires arguments on construct


#### Example PHP Code

```php
(object) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Cast\String_`

 * requires arguments on construct


#### Example PHP Code

```php
(string) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

### `PhpParser\Node\Expr\Cast\Unset_`

 * requires arguments on construct


#### Example PHP Code

```php
(unset) $value
```

#### Public Properties

 * `$expr` - `/** @var Expr Expression */`
<br>

## Children of "PhpParser\Node\Name"

### `PhpParser\Node\Name`

 * requires arguments on construct


#### Example PHP Code

```php
name
```

#### Public Properties

 * `$parts` - `/** @var string[] Parts of the name */`
 * `$specialClassNames` - ``
<br>

### `PhpParser\Node\Name\FullyQualified`

 * requires arguments on construct


#### Example PHP Code

```php
\name
```

#### Public Properties

 * `$parts` - `/** @var string[] Parts of the name */`
<br>

### `PhpParser\Node\Name\Relative`

 * requires arguments on construct


#### Example PHP Code

```php
namespace\name
```

#### Public Properties

 * `$parts` - `/** @var string[] Parts of the name */`
<br>

## Scalar nodes

### `PhpParser\Node\Scalar\DNumber`

 * requires arguments on construct


#### Example PHP Code

```php
10.5
```

#### Public Properties

 * `$value` - `/** @var float Number value */`
<br>

### `PhpParser\Node\Scalar\Encapsed`

 * requires arguments on construct


#### Example PHP Code

```php
"{$enscapsed}"
```

#### Public Properties

 * `$parts` - `/** @var Expr[] list of string parts */`
<br>

### `PhpParser\Node\Scalar\EncapsedStringPart`

 * requires arguments on construct


#### Example PHP Code

```php
UNABLE_TO_PRINT_ENCAPSED_STRING
```

#### Public Properties

 * `$value` - `/** @var string String value */`
<br>

### `PhpParser\Node\Scalar\LNumber`

 * requires arguments on construct


#### Example PHP Code

```php
100
```

#### Public Properties

 * `$value` - `/** @var int Number value */`
<br>

### `PhpParser\Node\Scalar\MagicConst\Class_`


#### Example PHP Code

```php
__CLASS__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\Dir`


#### Example PHP Code

```php
__DIR__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\File`


#### Example PHP Code

```php
__FILE__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\Function_`


#### Example PHP Code

```php
__FUNCTION__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\Line`


#### Example PHP Code

```php
__LINE__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\Method`


#### Example PHP Code

```php
__METHOD__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\Namespace_`


#### Example PHP Code

```php
__NAMESPACE__
```
<br>

### `PhpParser\Node\Scalar\MagicConst\Trait_`


#### Example PHP Code

```php
__TRAIT__
```
<br>

### `PhpParser\Node\Scalar\String_`

 * requires arguments on construct


#### Example PHP Code

```php
'string'
```

#### Public Properties

 * `$value` - `/** @var string String value */`
 * `$replacements` - ``
<br>

## Statements

### `PhpParser\Node\Stmt\Break_`


#### Example PHP Code

```php
break;
```

#### Public Properties

 * `$num` - `/** @var null|Node\Expr Number of loops to break */`
<br>

### `PhpParser\Node\Stmt\Case_`

 * requires arguments on construct


#### Example PHP Code

```php
case true:
```

#### Public Properties

 * `$cond` - `/** @var null|Node\Expr Condition (null for default) */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Catch_`

 * requires arguments on construct


#### Example PHP Code

```php
catch (CatchedType $catchedVariable) {
}
```

#### Public Properties

 * `$types` - `/** @var Node\Name[] Types of exceptions to catch */`
 * `$var` - `/** @var Expr\Variable Variable for exception */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\ClassConst`

 * requires arguments on construct


#### Example PHP Code

```php
const SOME_CLASS_CONSTANT = 'default value';
```

#### Public Properties

 * `$flags` - `/** @var int Modifiers */`
 * `$consts` - `/** @var Node\Const_[] Constant declarations */`
<br>

### `PhpParser\Node\Stmt\ClassMethod`

 * requires arguments on construct


#### Example PHP Code

```php
public function someMethod()
{
}
```

#### Public Properties

 * `$flags` - `/** @var int Flags */`
 * `$byRef` - `/** @var bool Whether to return by reference */`
 * `$name` - `/** @var Node\Identifier Name */`
 * `$params` - `/** @var Node\Param[] Parameters */`
 * `$returnType` - `/** @var null|Node\Identifier|Node\Name|Node\NullableType|Node\UnionType Return type */`
 * `$stmts` - `/** @var Node\Stmt[]|null Statements */`
 * `$magicNames` - ``
<br>

### `PhpParser\Node\Stmt\Class_`

 * requires arguments on construct


#### Example PHP Code

```php
class ClassName
{
}
```

#### Public Properties

 * `$flags` - `/** @var int Type */`
 * `$extends` - `/** @var null|Node\Name Name of extended class */`
 * `$implements` - `/** @var Node\Name[] Names of implemented interfaces */`
 * `$name` - `/** @var Node\Identifier|null Name */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Const_`

 * requires arguments on construct


#### Example PHP Code

```php
const CONSTANT_IN_CLASS = 'default value';
```

#### Public Properties

 * `$consts` - `/** @var Node\Const_[] Constant declarations */`
<br>

### `PhpParser\Node\Stmt\Continue_`


#### Example PHP Code

```php
continue;
```

#### Public Properties

 * `$num` - `/** @var null|Node\Expr Number of loops to continue */`
<br>

### `PhpParser\Node\Stmt\DeclareDeclare`

 * requires arguments on construct


#### Example PHP Code

```php
strict_types=1
```

#### Public Properties

 * `$key` - `/** @var Node\Identifier Key */`
 * `$value` - `/** @var Node\Expr Value */`
<br>

### `PhpParser\Node\Stmt\Declare_`

 * requires arguments on construct


#### Example PHP Code

```php
declare(strict_types=1);
```

#### Public Properties

 * `$declares` - `/** @var DeclareDeclare[] List of declares */`
 * `$stmts` - `/** @var Node\Stmt[]|null Statements */`
<br>

### `PhpParser\Node\Stmt\Do_`

 * requires arguments on construct


#### Example PHP Code

```php
do {
} while ($variable);
```

#### Public Properties

 * `$stmts` - `/** @var Node\Stmt[] Statements */`
 * `$cond` - `/** @var Node\Expr Condition */`
<br>

### `PhpParser\Node\Stmt\Echo_`

 * requires arguments on construct


#### Example PHP Code

```php
echo 'hello';
```

#### Public Properties

 * `$exprs` - `/** @var Node\Expr[] Expressions */`
<br>

### `PhpParser\Node\Stmt\ElseIf_`

 * requires arguments on construct


#### Example PHP Code

```php
elseif (true) {
}
```

#### Public Properties

 * `$cond` - `/** @var Node\Expr Condition */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Else_`


#### Example PHP Code

```php
else {
}
```

#### Public Properties

 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Expression`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable;
```

#### Public Properties

 * `$expr` - `/** @var Node\Expr Expression */`
<br>

### `PhpParser\Node\Stmt\Finally_`


#### Example PHP Code

```php
finally {
}
```

#### Public Properties

 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\For_`


#### Example PHP Code

```php
for (;;) {
}
```

#### Public Properties

 * `$init` - `/** @var Node\Expr[] Init expressions */`
 * `$cond` - `/** @var Node\Expr[] Loop conditions */`
 * `$loop` - `/** @var Node\Expr[] Loop expressions */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Foreach_`

 * requires arguments on construct


#### Example PHP Code

```php
foreach ($variables as $value) {
}
```

#### Public Properties

 * `$expr` - `/** @var Node\Expr Expression to iterate */`
 * `$keyVar` - `/** @var null|Node\Expr Variable to assign key to */`
 * `$byRef` - `/** @var bool Whether to assign value by reference */`
 * `$valueVar` - `/** @var Node\Expr Variable to assign value to */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Function_`

 * requires arguments on construct


#### Example PHP Code

```php
function some_function()
{
}
```

#### Public Properties

 * `$byRef` - `/** @var bool Whether function returns by reference */`
 * `$name` - `/** @var Node\Identifier Name */`
 * `$params` - `/** @var Node\Param[] Parameters */`
 * `$returnType` - `/** @var null|Node\Identifier|Node\Name|Node\NullableType|Node\UnionType Return type */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Global_`

 * requires arguments on construct


#### Example PHP Code

```php
global $globalVariable;
```

#### Public Properties

 * `$vars` - `/** @var Node\Expr[] Variables */`
<br>

### `PhpParser\Node\Stmt\Goto_`

 * requires arguments on construct


#### Example PHP Code

```php
goto goto_break;
```

#### Public Properties

 * `$name` - `/** @var Identifier Name of label to jump to */`
<br>

### `PhpParser\Node\Stmt\GroupUse`

 * requires arguments on construct


#### Example PHP Code

```php
use prefix\{UsedNamespace};
```

#### Public Properties

 * `$type` - `/** @var int Type of group use */`
 * `$prefix` - `/** @var Name Prefix for uses */`
 * `$uses` - `/** @var UseUse[] Uses */`
<br>

### `PhpParser\Node\Stmt\HaltCompiler`

 * requires arguments on construct


#### Example PHP Code

```php
__halt_compiler();remaining
```

#### Public Properties

 * `$remaining` - `/** @var string Remaining text after halt compiler statement. */`
<br>

### `PhpParser\Node\Stmt\If_`

 * requires arguments on construct


#### Example PHP Code

```php
if (true) {
}
```

#### Public Properties

 * `$cond` - `/** @var Node\Expr Condition expression */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
 * `$elseifs` - `/** @var ElseIf_[] Elseif clauses */`
 * `$else` - `/** @var null|Else_ Else clause */`
<br>

### `PhpParser\Node\Stmt\InlineHTML`

 * requires arguments on construct


#### Example PHP Code

```php
?>
<strong>feel</strong><?php
```

#### Public Properties

 * `$value` - `/** @var string String */`
<br>

### `PhpParser\Node\Stmt\Interface_`

 * requires arguments on construct


#### Example PHP Code

```php
interface SomeInterface
{
}
```

#### Public Properties

 * `$extends` - `/** @var Node\Name[] Extended interfaces */`
 * `$name` - `/** @var Node\Identifier|null Name */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Label`

 * requires arguments on construct


#### Example PHP Code

```php
label:
```

#### Public Properties

 * `$name` - `/** @var Identifier Name */`
<br>

### `PhpParser\Node\Stmt\Namespace_`


#### Example PHP Code

```php
namespace {
}
```

#### Public Properties

 * `$name` - `/** @var null|Node\Name Name */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\Nop`


#### Example PHP Code

```php

```
<br>

### `PhpParser\Node\Stmt\Property`

 * requires arguments on construct


#### Example PHP Code

```php
var $property;
```

#### Public Properties

 * `$flags` - `/** @var int Modifiers */`
 * `$props` - `/** @var PropertyProperty[] Properties */`
 * `$type` - `/** @var null|Identifier|Name|NullableType|UnionType Type declaration */`
<br>

### `PhpParser\Node\Stmt\PropertyProperty`

 * requires arguments on construct


#### Example PHP Code

```php
$someProperty
```

#### Public Properties

 * `$name` - `/** @var Node\VarLikeIdentifier Name */`
 * `$default` - `/** @var null|Node\Expr Default */`
<br>

### `PhpParser\Node\Stmt\Return_`


#### Example PHP Code

```php
return;
```

#### Public Properties

 * `$expr` - `/** @var null|Node\Expr Expression */`
<br>

### `PhpParser\Node\Stmt\StaticVar`

 * requires arguments on construct


#### Example PHP Code

```php
$variable
```

#### Public Properties

 * `$var` - `/** @var Expr\Variable Variable */`
 * `$default` - `/** @var null|Node\Expr Default value */`
<br>

### `PhpParser\Node\Stmt\Static_`

 * requires arguments on construct


#### Example PHP Code

```php
static $static;
```

#### Public Properties

 * `$vars` - `/** @var StaticVar[] Variable definitions */`
<br>

### `PhpParser\Node\Stmt\Switch_`

 * requires arguments on construct


#### Example PHP Code

```php
switch ($variable) {
    case 1:
}
```

#### Public Properties

 * `$cond` - `/** @var Node\Expr Condition */`
 * `$cases` - `/** @var Case_[] Case list */`
<br>

### `PhpParser\Node\Stmt\Throw_`

 * requires arguments on construct


#### Example PHP Code

```php
throw new \SomeException();
```

#### Public Properties

 * `$expr` - `/** @var Node\Expr Expression */`
<br>

### `PhpParser\Node\Stmt\TraitUse`

 * requires arguments on construct


#### Example PHP Code

```php
use trait;
```

#### Public Properties

 * `$traits` - `/** @var Node\Name[] Traits */`
 * `$adaptations` - `/** @var TraitUseAdaptation[] Adaptations */`
<br>

### `PhpParser\Node\Stmt\TraitUseAdaptation\Alias`

 * requires arguments on construct


#### Example PHP Code

```php
SomeTrait::method as public aliasedMethod;
```

#### Public Properties

 * `$newModifier` - `/** @var null|int New modifier */`
 * `$newName` - `/** @var null|Node\Identifier New name */`
 * `$trait` - `/** @var Node\Name|null Trait name */`
 * `$method` - `/** @var Node\Identifier Method name */`
<br>

### `PhpParser\Node\Stmt\TraitUseAdaptation\Precedence`

 * requires arguments on construct


#### Example PHP Code

```php
SomeTrait::someMethod insteadof overriddenTrait;
```

#### Public Properties

 * `$insteadof` - `/** @var Node\Name[] Overwritten traits */`
 * `$trait` - `/** @var Node\Name|null Trait name */`
 * `$method` - `/** @var Node\Identifier Method name */`
<br>

### `PhpParser\Node\Stmt\Trait_`

 * requires arguments on construct


#### Example PHP Code

```php
trait TraitName
{
}
```

#### Public Properties

 * `$name` - `/** @var Node\Identifier|null Name */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

### `PhpParser\Node\Stmt\TryCatch`

 * requires arguments on construct


#### Example PHP Code

```php
try {
    function someFunction()
    {
    }
} catch (\SomeType $someTypeException) {
}
```

#### Public Properties

 * `$stmts` - `/** @var Node\Stmt[] Statements */`
 * `$catches` - `/** @var Catch_[] Catches */`
 * `$finally` - `/** @var null|Finally_ Optional finally node */`
<br>

### `PhpParser\Node\Stmt\Unset_`

 * requires arguments on construct


#### Example PHP Code

```php
unset($variable);
```

#### Public Properties

 * `$vars` - `/** @var Node\Expr[] Variables to unset */`
<br>

### `PhpParser\Node\Stmt\UseUse`

 * requires arguments on construct


#### Example PHP Code

```php
UsedNamespace
```

#### Public Properties

 * `$type` - `/** @var int One of the Stmt\Use_::TYPE_* constants. Will only differ from TYPE_UNKNOWN for mixed group uses */`
 * `$name` - `/** @var Node\Name Namespace, class, function or constant to alias */`
 * `$alias` - `/** @var Identifier|null Alias */`
<br>

### `PhpParser\Node\Stmt\Use_`

 * requires arguments on construct


#### Example PHP Code

```php
use UsedNamespace;
```

#### Public Properties

 * `$type` - `/** @var int Type of alias */`
 * `$uses` - `/** @var UseUse[] Aliases */`
<br>

### `PhpParser\Node\Stmt\While_`

 * requires arguments on construct


#### Example PHP Code

```php
while ($variable) {
}
```

#### Public Properties

 * `$cond` - `/** @var Node\Expr Condition */`
 * `$stmts` - `/** @var Node\Stmt[] Statements */`
<br>

## Various

### `PhpParser\Node\Arg`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable
```

#### Public Properties

 * `$value` - `/** @var Expr Value to pass */`
 * `$byRef` - `/** @var bool Whether to pass by ref */`
 * `$unpack` - `/** @var bool Whether to unpack the argument */`
<br>

### `PhpParser\Node\Const_`

 * requires arguments on construct


#### Example PHP Code

```php
CONSTANT_NAME = 'default'
```

#### Public Properties

 * `$name` - `/** @var Identifier Name */`
 * `$value` - `/** @var Expr Value */`
<br>

### `PhpParser\Node\Identifier`

 * requires arguments on construct


#### Example PHP Code

```php
identifier
```

#### Public Properties

 * `$name` - `/** @var string Identifier as string */`
 * `$specialClassNames` - ``
<br>

### `PhpParser\Node\NullableType`

 * requires arguments on construct


#### Example PHP Code

```php
?SomeType
```

#### Public Properties

 * `$type` - `/** @var Identifier|Name Type */`
<br>

### `PhpParser\Node\Param`

 * requires arguments on construct


#### Example PHP Code

```php
$someVariable
```

#### Public Properties

 * `$type` - `/** @var null|Identifier|Name|NullableType|UnionType Type declaration */`
 * `$byRef` - `/** @var bool Whether parameter is passed by reference */`
 * `$variadic` - `/** @var bool Whether this is a variadic argument */`
 * `$var` - `/** @var Expr\Variable|Expr\Error Parameter variable */`
 * `$default` - `/** @var null|Expr Default value */`
<br>

### `PhpParser\Node\UnionType`

 * requires arguments on construct


#### Example PHP Code

```php
string|null
```

#### Public Properties

 * `$types` - `/** @var (Identifier|Name)[] Types */`
<br>

