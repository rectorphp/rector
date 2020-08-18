<?php

namespace Rector\Laravel\Tests\Rector\ArrayItem\InlineValidationRulesToArrayDefinitionRector\Fixture;

use Illuminate\Foundation\Http\FormRequest;

class SomeClass extends FormRequest
{
    public function rules(): array
    {
        return [
            'someAttribute' => 'required|string|exists:' . SomeModel::class . ',id',
            'attribute2' => 'required',
            'attribute3' => 'required|unsupportedRule:' . SomeRule::class,
        ];
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\ArrayItem\InlineValidationRulesToArrayDefinitionRector\Fixture;

use Illuminate\Foundation\Http\FormRequest;

class SomeClass extends FormRequest
{
    public function rules(): array
    {
        return [
            'someAttribute' => ['required', 'string', \Illuminate\Validation\Rule::exists(SomeModel::class, 'id')],
            'attribute2' => ['required'],
            'attribute3' => ['required', 'unsupportedRule:' . SomeRule::class],
        ];
    }
}

?>
