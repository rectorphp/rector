# Node Overview

## Expressions

#### `PhpParser\Node\Expr\ArrayDimFetch`

```php
$someVariable[0]
```
<br>

#### `PhpParser\Node\Expr\ArrayItem`

```php
'name' => $Tom
```
<br>

#### `PhpParser\Node\Expr\Array_`

```php
[]
```
<br>

#### `PhpParser\Node\Expr\ArrowFunction`

```php
fn() => 1
```
<br>

#### `PhpParser\Node\Expr\Assign`

```php
$someVariable = 'some value'
```
<br>

#### `PhpParser\Node\Expr\AssignRef`

```php
$someVariable =& $someOtherVariable
```
<br>

#### `PhpParser\Node\Expr\BitwiseNot`

```php
~$someVariable
```
<br>

#### `PhpParser\Node\Expr\BooleanNot`

```php
!true
```
<br>

#### `PhpParser\Node\Expr\ClassConstFetch`

```php
SomeClass::SOME_CONSTANT
```
<br>

#### `PhpParser\Node\Expr\Clone_`

```php
clone $someVariable
```
<br>

#### `PhpParser\Node\Expr\Closure`

```php
function () {
}
```
<br>

#### `PhpParser\Node\Expr\ClosureUse`

```php
$someVariable
```
<br>

#### `PhpParser\Node\Expr\ConstFetch`

```php
true
```
<br>

#### `PhpParser\Node\Expr\Empty_`

```php
empty($someVariable)
```
<br>

#### `PhpParser\Node\Expr\ErrorSuppress`

```php
@$someVariable
```
<br>

#### `PhpParser\Node\Expr\Eval_`

```php
eval('Some php code')
```
<br>

#### `PhpParser\Node\Expr\Exit_`

```php
die
```
<br>

#### `PhpParser\Node\Expr\FuncCall`

```php
functionCall()
```
<br>

#### `PhpParser\Node\Expr\Include_`

```php
include $someVariable
```
<br>

#### `PhpParser\Node\Expr\Instanceof_`

```php
$someVariable instanceof SomeClass
```
<br>

#### `PhpParser\Node\Expr\Isset_`

```php
isset($variable)
```
<br>

#### `PhpParser\Node\Expr\List_`

```php
list($someVariable)
```
<br>

#### `PhpParser\Node\Expr\MethodCall`

```php
$someObject->methodName()
```
<br>

#### `PhpParser\Node\Expr\New_`

```php
new class
{
}
```
<br>

#### `PhpParser\Node\Expr\PostDec`

```php
$someVariable--
```
<br>

#### `PhpParser\Node\Expr\PostInc`

```php
$someVariable++
```
<br>

#### `PhpParser\Node\Expr\PreDec`

```php
--$someVariable
```
<br>

#### `PhpParser\Node\Expr\PreInc`

```php
++$someVariable
```
<br>

#### `PhpParser\Node\Expr\Print_`

```php
print $someVariable
```
<br>

#### `PhpParser\Node\Expr\PropertyFetch`

```php
$someVariable->propertyName
```
<br>

#### `PhpParser\Node\Expr\ShellExec`

```php
`encapsedstring`
```
<br>

#### `PhpParser\Node\Expr\StaticCall`

```php
SomeClass::methodName()
```
<br>

#### `PhpParser\Node\Expr\StaticPropertyFetch`

```php
SomeClass::$someProperty
```
<br>

#### `PhpParser\Node\Expr\Ternary`

```php
$someVariable ? true : false
```
<br>

#### `PhpParser\Node\Expr\UnaryMinus`

```php
-$someVariable
```
<br>

#### `PhpParser\Node\Expr\UnaryPlus`

```php
+$someVariable
```
<br>

#### `PhpParser\Node\Expr\Variable`

```php
$someVariable
```
<br>

#### `PhpParser\Node\Expr\YieldFrom`

```php
yield from $someVariable
```
<br>

#### `PhpParser\Node\Expr\Yield_`

```php
yield
```
<br>

## Children of "PhpParser\Node\Expr\AssignOp"

#### `PhpParser\Node\Expr\AssignOp\BitwiseAnd`

```php
$variable &= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\BitwiseOr`

```php
$variable |= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\BitwiseXor`

```php
$variable ^= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Coalesce`

```php
$variable ??= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Concat`

```php
$variable .= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Div`

```php
$variable /= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Minus`

```php
$variable -= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Mod`

```php
$variable %= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Mul`

```php
$variable *= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Plus`

```php
$variable += 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\Pow`

```php
$variable **= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\ShiftLeft`

```php
$variable <<= 'value'
```
<br>

#### `PhpParser\Node\Expr\AssignOp\ShiftRight`

```php
$variable >>= 'value'
```
<br>

## Children of "PhpParser\Node\Expr\BinaryOp"

#### `PhpParser\Node\Expr\BinaryOp\BitwiseAnd`

```php
1 & 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\BitwiseOr`

```php
1 | 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\BitwiseXor`

```php
1 ^ 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\BooleanAnd`

```php
1 && 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\BooleanOr`

```php
1 || 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Coalesce`

```php
1 ?? 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Concat`

```php
1 . 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Div`

```php
1 / 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Equal`

```php
1 == 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Greater`

```php
1 > 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\GreaterOrEqual`

```php
1 >= 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Identical`

```php
1 === 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\LogicalAnd`

```php
1 and 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\LogicalOr`

```php
1 or 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\LogicalXor`

```php
1 xor 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Minus`

```php
1 - 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Mod`

```php
1 % 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Mul`

```php
1 * 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\NotEqual`

```php
1 != 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\NotIdentical`

```php
1 !== 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Plus`

```php
1 + 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Pow`

```php
1 ** 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\ShiftLeft`

```php
1 << 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\ShiftRight`

```php
1 >> 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Smaller`

```php
1 < 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\SmallerOrEqual`

```php
1 <= 'a'
```
<br>

#### `PhpParser\Node\Expr\BinaryOp\Spaceship`

```php
1 <=> 'a'
```
<br>

## Children of "PhpParser\Node\Expr\Cast"

#### `PhpParser\Node\Expr\Cast\Array_`

```php
(array) $value
```
<br>

#### `PhpParser\Node\Expr\Cast\Bool_`

```php
(bool) $value
```
<br>

#### `PhpParser\Node\Expr\Cast\Double`

```php
(double) $value
```
<br>

#### `PhpParser\Node\Expr\Cast\Int_`

```php
(int) $value
```
<br>

#### `PhpParser\Node\Expr\Cast\Object_`

```php
(object) $value
```
<br>

#### `PhpParser\Node\Expr\Cast\String_`

```php
(string) $value
```
<br>

#### `PhpParser\Node\Expr\Cast\Unset_`

```php
(unset) $value
```
<br>

## Children of "PhpParser\Node\Name"

#### `PhpParser\Node\Name`

```php
name
```
<br>

#### `PhpParser\Node\Name\FullyQualified`

```php
\name
```
<br>

#### `PhpParser\Node\Name\Relative`

```php
namespace\name
```
<br>

## Scalar nodes

#### `PhpParser\Node\Scalar\DNumber`

```php
10.5
```
<br>

#### `PhpParser\Node\Scalar\Encapsed`

```php
"{$enscapsed}"
```
<br>

#### `PhpParser\Node\Scalar\EncapsedStringPart`

```php
UNABLE_TO_PRINT_ENCAPSED_STRING
```
<br>

#### `PhpParser\Node\Scalar\LNumber`

```php
100
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Class_`

```php
__CLASS__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Dir`

```php
__DIR__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\File`

```php
__FILE__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Function_`

```php
__FUNCTION__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Line`

```php
__LINE__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Method`

```php
__METHOD__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Namespace_`

```php
__NAMESPACE__
```
<br>

#### `PhpParser\Node\Scalar\MagicConst\Trait_`

```php
__TRAIT__
```
<br>

#### `PhpParser\Node\Scalar\String_`

```php
'string'
```
<br>

## Statements

#### `PhpParser\Node\Stmt\Break_`

```php
break;
```
<br>

#### `PhpParser\Node\Stmt\Case_`

```php
case true:
```
<br>

#### `PhpParser\Node\Stmt\Catch_`

```php
catch (CatchedType $catchedVariable) {
}
```
<br>

#### `PhpParser\Node\Stmt\ClassConst`

```php
const SOME_CLASS_CONSTANT = 'default value';
```
<br>

#### `PhpParser\Node\Stmt\ClassMethod`

```php
function someMethod()
{
}
```
<br>

#### `PhpParser\Node\Stmt\Class_`

```php
class ClassName
{
}
```
<br>

#### `PhpParser\Node\Stmt\Const_`

```php
const CONSTANT_IN_CLASS = 'default value';
```
<br>

#### `PhpParser\Node\Stmt\Continue_`

```php
continue;
```
<br>

#### `PhpParser\Node\Stmt\DeclareDeclare`

```php
strict_types=1
```
<br>

#### `PhpParser\Node\Stmt\Declare_`

```php
declare (strict_types=1);
```
<br>

#### `PhpParser\Node\Stmt\Do_`

```php
do {
} while ($variable);
```
<br>

#### `PhpParser\Node\Stmt\Echo_`

```php
echo 'hello';
```
<br>

#### `PhpParser\Node\Stmt\ElseIf_`

```php
elseif (true) {
}
```
<br>

#### `PhpParser\Node\Stmt\Else_`

```php
else {
}
```
<br>

#### `PhpParser\Node\Stmt\Expression`

```php
$someVariable;
```
<br>

#### `PhpParser\Node\Stmt\Finally_`

```php
finally {
}
```
<br>

#### `PhpParser\Node\Stmt\For_`

```php
for (;;) {
}
```
<br>

#### `PhpParser\Node\Stmt\Foreach_`

```php
foreach ($variables as $value) {
}
```
<br>

#### `PhpParser\Node\Stmt\Function_`

```php
function some_function()
{
}
```
<br>

#### `PhpParser\Node\Stmt\Global_`

```php
global $globalVariable;
```
<br>

#### `PhpParser\Node\Stmt\Goto_`

```php
goto goto_break;
```
<br>

#### `PhpParser\Node\Stmt\GroupUse`

```php
use prefix\{UsedNamespace};
```
<br>

#### `PhpParser\Node\Stmt\HaltCompiler`

```php
__halt_compiler();remaining
```
<br>

#### `PhpParser\Node\Stmt\If_`

```php
if (true) {
}
```
<br>

#### `PhpParser\Node\Stmt\InlineHTML`

```php
?>
<strong>feel</strong><?php
```
<br>

#### `PhpParser\Node\Stmt\Interface_`

```php
interface SomeInterface
{
}
```
<br>

#### `PhpParser\Node\Stmt\Label`

```php
label:
```
<br>

#### `PhpParser\Node\Stmt\Namespace_`

```php
namespace {
}
```
<br>

#### `PhpParser\Node\Stmt\Nop`

```php

```
<br>

#### `PhpParser\Node\Stmt\Property`

```php
var $property;
```
<br>

#### `PhpParser\Node\Stmt\PropertyProperty`

```php
$someProperty
```
<br>

#### `PhpParser\Node\Stmt\Return_`

```php
return;
```
<br>

#### `PhpParser\Node\Stmt\StaticVar`

```php
$variable
```
<br>

#### `PhpParser\Node\Stmt\Static_`

```php
static $static;
```
<br>

#### `PhpParser\Node\Stmt\Switch_`

```php
switch ($variable) {
    case 1:
}
```
<br>

#### `PhpParser\Node\Stmt\Throw_`

```php
throw new $someException();
```
<br>

#### `PhpParser\Node\Stmt\TraitUse`

```php
use trait;
```
<br>

#### `PhpParser\Node\Stmt\TraitUseAdaptation\Alias`

```php
SomeTrait::method as public aliasedMethod;
```
<br>

#### `PhpParser\Node\Stmt\TraitUseAdaptation\Precedence`

```php
SomeTrait::someMethod insteadof overriddenTrait;
```
<br>

#### `PhpParser\Node\Stmt\Trait_`

```php
trait TraitName
{
}
```
<br>

#### `PhpParser\Node\Stmt\TryCatch`

```php
try {
    function someFunction()
    {
    }
} function logException()
{
}
```
<br>

#### `PhpParser\Node\Stmt\Unset_`

```php
unset($variable);
```
<br>

#### `PhpParser\Node\Stmt\UseUse`

```php
UsedNamespace
```
<br>

#### `PhpParser\Node\Stmt\Use_`

```php
use UsedNamespace;
```
<br>

#### `PhpParser\Node\Stmt\While_`

```php
while ($variable) {
}
```
<br>

## Various

#### `PhpParser\Node\Arg`

```php
$someVariable
```
<br>

#### `PhpParser\Node\Const_`

```php
CONSTANT_NAME = 'default'
```
<br>

#### `PhpParser\Node\Identifier`

```php
identifier
```
<br>

#### `PhpParser\Node\NullableType`

```php
?SomeType
```
<br>

#### `PhpParser\Node\Param`

```php
$someVariable
```
<br>

