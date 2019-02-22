# Node Overview

## Expressions

### `PhpParser\Node\Expr\ArrayDimFetch`

```php
$someVariable[0]
```

### `PhpParser\Node\Expr\ArrayItem`

```php
'name' => $Tom
```

### `PhpParser\Node\Expr\Array_`

```php
[]
```

### `PhpParser\Node\Expr\Assign`

```php
$someVariable = 'some value'
```

### `PhpParser\Node\Expr\AssignRef`

```php
$someVariable =& $someOtherVariable
```

### `PhpParser\Node\Expr\BitwiseNot`

```php
~$someVariable
```

### `PhpParser\Node\Expr\BooleanNot`

```php
!true
```

### `PhpParser\Node\Expr\ClassConstFetch`

```php
SomeClass::SOME_CONSTANT
```

### `PhpParser\Node\Expr\Clone_`

```php
clone $someVariable
```

### `PhpParser\Node\Expr\Closure`

```php
function () {
}
```

### `PhpParser\Node\Expr\ClosureUse`

```php
$someVariable
```

### `PhpParser\Node\Expr\ConstFetch`

```php
true
```

### `PhpParser\Node\Expr\Empty_`

```php
empty($someVariable)
```

### `PhpParser\Node\Expr\ErrorSuppress`

```php
@$someVariable
```

### `PhpParser\Node\Expr\Eval_`

```php
eval('Some php code')
```

### `PhpParser\Node\Expr\Exit_`

```php
die
```

### `PhpParser\Node\Expr\FuncCall`

```php
functionCall()
```

### `PhpParser\Node\Expr\Include_`

```php
include $someVariable
```

### `PhpParser\Node\Expr\Instanceof_`

```php
$someVariable instanceof SomeClass
```

### `PhpParser\Node\Expr\Isset_`

```php
isset($variable)
```

### `PhpParser\Node\Expr\List_`

```php
list($someVariable)
```

### `PhpParser\Node\Expr\MethodCall`

```php
$someObject->methodName()
```

### `PhpParser\Node\Expr\New_`

```php
new class
{
}
```

### `PhpParser\Node\Expr\PostDec`

```php
$someVariable--
```

### `PhpParser\Node\Expr\PostInc`

```php
$someVariable++
```

### `PhpParser\Node\Expr\PreDec`

```php
--$someVariable
```

### `PhpParser\Node\Expr\PreInc`

```php
++$someVariable
```

### `PhpParser\Node\Expr\Print_`

```php
print $someVariable
```

### `PhpParser\Node\Expr\PropertyFetch`

```php
$someVariable->propertyName
```

### `PhpParser\Node\Expr\ShellExec`

```php
`encapsedstring`
```

### `PhpParser\Node\Expr\StaticCall`

```php
SomeClass::methodName()
```

### `PhpParser\Node\Expr\StaticPropertyFetch`

```php
SomeClass::$someProperty
```

### `PhpParser\Node\Expr\Ternary`

```php
$someVariable ? true : false
```

### `PhpParser\Node\Expr\UnaryMinus`

```php
-$someVariable
```

### `PhpParser\Node\Expr\UnaryPlus`

```php
+$someVariable
```

### `PhpParser\Node\Expr\Variable`

```php
$someVariable
```

### `PhpParser\Node\Expr\YieldFrom`

```php
yield from $someVariable
```

### `PhpParser\Node\Expr\Yield_`

```php
yield
```

## Children of "PhpParser\Node\Expr\AssignOp"

### `PhpParser\Node\Expr\AssignOp\BitwiseAnd`

```php
$variable &= 'value'
```

### `PhpParser\Node\Expr\AssignOp\BitwiseOr`

```php
$variable |= 'value'
```

### `PhpParser\Node\Expr\AssignOp\BitwiseXor`

```php
$variable ^= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Coalesce`

```php
$variable ??= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Concat`

```php
$variable .= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Div`

```php
$variable /= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Minus`

```php
$variable -= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Mod`

```php
$variable %= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Mul`

```php
$variable *= 'value'
```

### `PhpParser\Node\Expr\AssignOp\Plus`

```php
$variable += 'value'
```

### `PhpParser\Node\Expr\AssignOp\Pow`

```php
$variable **= 'value'
```

### `PhpParser\Node\Expr\AssignOp\ShiftLeft`

```php
$variable <<= 'value'
```

### `PhpParser\Node\Expr\AssignOp\ShiftRight`

```php
$variable >>= 'value'
```

## Children of "PhpParser\Node\Expr\BinaryOp"

### `PhpParser\Node\Expr\BinaryOp\BitwiseAnd`

```php
1 & 'a'
```

### `PhpParser\Node\Expr\BinaryOp\BitwiseOr`

```php
1 | 'a'
```

### `PhpParser\Node\Expr\BinaryOp\BitwiseXor`

```php
1 ^ 'a'
```

### `PhpParser\Node\Expr\BinaryOp\BooleanAnd`

```php
1 && 'a'
```

### `PhpParser\Node\Expr\BinaryOp\BooleanOr`

```php
1 || 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Coalesce`

```php
1 ?? 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Concat`

```php
1 . 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Div`

```php
1 / 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Equal`

```php
1 == 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Greater`

```php
1 > 'a'
```

### `PhpParser\Node\Expr\BinaryOp\GreaterOrEqual`

```php
1 >= 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Identical`

```php
1 === 'a'
```

### `PhpParser\Node\Expr\BinaryOp\LogicalAnd`

```php
1 and 'a'
```

### `PhpParser\Node\Expr\BinaryOp\LogicalOr`

```php
1 or 'a'
```

### `PhpParser\Node\Expr\BinaryOp\LogicalXor`

```php
1 xor 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Minus`

```php
1 - 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Mod`

```php
1 % 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Mul`

```php
1 * 'a'
```

### `PhpParser\Node\Expr\BinaryOp\NotEqual`

```php
1 != 'a'
```

### `PhpParser\Node\Expr\BinaryOp\NotIdentical`

```php
1 !== 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Plus`

```php
1 + 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Pow`

```php
1 ** 'a'
```

### `PhpParser\Node\Expr\BinaryOp\ShiftLeft`

```php
1 << 'a'
```

### `PhpParser\Node\Expr\BinaryOp\ShiftRight`

```php
1 >> 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Smaller`

```php
1 < 'a'
```

### `PhpParser\Node\Expr\BinaryOp\SmallerOrEqual`

```php
1 <= 'a'
```

### `PhpParser\Node\Expr\BinaryOp\Spaceship`

```php
1 <=> 'a'
```

## Children of "PhpParser\Node\Expr\Cast"

### `PhpParser\Node\Expr\Cast\Array_`

```php
(array) $value
```

### `PhpParser\Node\Expr\Cast\Bool_`

```php
(bool) $value
```

### `PhpParser\Node\Expr\Cast\Double`

```php
(double) $value
```

### `PhpParser\Node\Expr\Cast\Int_`

```php
(int) $value
```

### `PhpParser\Node\Expr\Cast\Object_`

```php
(object) $value
```

### `PhpParser\Node\Expr\Cast\String_`

```php
(string) $value
```

### `PhpParser\Node\Expr\Cast\Unset_`

```php
(unset) $value
```

## Children of "PhpParser\Node\Name"

### `PhpParser\Node\Name`

```php
name
```

### `PhpParser\Node\Name\FullyQualified`

```php
\name
```

### `PhpParser\Node\Name\Relative`

```php
namespace\name
```

## Scalar nodes

### `PhpParser\Node\Scalar\DNumber`

```php
10.5
```

### `PhpParser\Node\Scalar\Encapsed`

```php
"{$enscapsed}"
```

### `PhpParser\Node\Scalar\EncapsedStringPart`

```php
UNABLE_TO_PRINT_ENCAPSED_STRING
```

### `PhpParser\Node\Scalar\LNumber`

```php
100
```

### `PhpParser\Node\Scalar\MagicConst\Class_`

```php
__CLASS__
```

### `PhpParser\Node\Scalar\MagicConst\Dir`

```php
__DIR__
```

### `PhpParser\Node\Scalar\MagicConst\File`

```php
__FILE__
```

### `PhpParser\Node\Scalar\MagicConst\Function_`

```php
__FUNCTION__
```

### `PhpParser\Node\Scalar\MagicConst\Line`

```php
__LINE__
```

### `PhpParser\Node\Scalar\MagicConst\Method`

```php
__METHOD__
```

### `PhpParser\Node\Scalar\MagicConst\Namespace_`

```php
__NAMESPACE__
```

### `PhpParser\Node\Scalar\MagicConst\Trait_`

```php
__TRAIT__
```

### `PhpParser\Node\Scalar\String_`

```php
'string'
```

## Statements

### `PhpParser\Node\Stmt\Break_`

```php
break;
```

### `PhpParser\Node\Stmt\Case_`

```php
case true:
```

### `PhpParser\Node\Stmt\Catch_`

```php
catch (CatchedType $catchedVariable) {
}
```

### `PhpParser\Node\Stmt\ClassConst`

```php
const SOME_CLASS_CONSTANT = 'default value';
```

### `PhpParser\Node\Stmt\ClassMethod`

```php
function someMethod()
{
}
```

### `PhpParser\Node\Stmt\Class_`

```php
class ClassName
{
}
```

### `PhpParser\Node\Stmt\Const_`

```php
const CONSTANT_IN_CLASS = 'default value';
```

### `PhpParser\Node\Stmt\Continue_`

```php
continue;
```

### `PhpParser\Node\Stmt\DeclareDeclare`

```php
strict_types=1
```

### `PhpParser\Node\Stmt\Declare_`

```php
declare (strict_types=1);
```

### `PhpParser\Node\Stmt\Do_`

```php
do {
} while ($variable);
```

### `PhpParser\Node\Stmt\Echo_`

```php
echo 'hello';
```

### `PhpParser\Node\Stmt\ElseIf_`

```php
elseif (true) {
}
```

### `PhpParser\Node\Stmt\Else_`

```php
else {
}
```

### `PhpParser\Node\Stmt\Expression`

```php
$someVariable;
```

### `PhpParser\Node\Stmt\Finally_`

```php
finally {
}
```

### `PhpParser\Node\Stmt\For_`

```php
for (;;) {
}
```

### `PhpParser\Node\Stmt\Foreach_`

```php
foreach ($variables as $value) {
}
```

### `PhpParser\Node\Stmt\Function_`

```php
function some_function()
{
}
```

### `PhpParser\Node\Stmt\Global_`

```php
global $globalVariable;
```

### `PhpParser\Node\Stmt\Goto_`

```php
goto goto_break;
```

### `PhpParser\Node\Stmt\GroupUse`

```php
use prefix\{UsedNamespace};
```

### `PhpParser\Node\Stmt\HaltCompiler`

```php
__halt_compiler();remaining
```

### `PhpParser\Node\Stmt\If_`

```php
if (true) {
}
```

### `PhpParser\Node\Stmt\InlineHTML`

```php
?>
<strong>feel</strong><?php
```

### `PhpParser\Node\Stmt\Interface_`

```php
interface SomeInterface
{
}
```

### `PhpParser\Node\Stmt\Label`

```php
label:
```

### `PhpParser\Node\Stmt\Namespace_`

```php
namespace {
}
```

### `PhpParser\Node\Stmt\Nop`

```php

```

### `PhpParser\Node\Stmt\Property`

```php
var $property;
```

### `PhpParser\Node\Stmt\PropertyProperty`

```php
$someProperty
```

### `PhpParser\Node\Stmt\Return_`

```php
return;
```

### `PhpParser\Node\Stmt\StaticVar`

```php
$variable
```

### `PhpParser\Node\Stmt\Static_`

```php
static $static;
```

### `PhpParser\Node\Stmt\Switch_`

```php
switch ($variable) {
    case 1:
}
```

### `PhpParser\Node\Stmt\Throw_`

```php
throw new $someException();
```

### `PhpParser\Node\Stmt\TraitUse`

```php
use trait;
```

### `PhpParser\Node\Stmt\TraitUseAdaptation\Alias`

```php
SomeTrait::method as public aliasedMethod;
```

### `PhpParser\Node\Stmt\TraitUseAdaptation\Precedence`

```php
SomeTrait::someMethod insteadof overriddenTrait;
```

### `PhpParser\Node\Stmt\Trait_`

```php
trait TraitName
{
}
```

### `PhpParser\Node\Stmt\TryCatch`

```php
try {
    function someFunction()
    {
    }
} function logException()
{
}
```

### `PhpParser\Node\Stmt\Unset_`

```php
unset($variable);
```

### `PhpParser\Node\Stmt\UseUse`

```php
UsedNamespace
```

### `PhpParser\Node\Stmt\Use_`

```php
use UsedNamespace;
```

### `PhpParser\Node\Stmt\While_`

```php
while ($variable) {
}
```

## Various

### `PhpParser\Node\Arg`

```php
$someVariable
```

### `PhpParser\Node\Const_`

```php
CONSTANT_NAME = 'default'
```

### `PhpParser\Node\Identifier`

```php
identifier
```

### `PhpParser\Node\NullableType`

```php
?SomeType
```

### `PhpParser\Node\Param`

```php
$someVariable
```

