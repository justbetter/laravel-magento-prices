<?php

namespace JustBetter\MagentoPrices\Concerns;

use Illuminate\Support\Facades\Validator;

/**
 * @method array toArray()
 */
trait ValidatesData
{
    public array $rules = [];

    public function validate(array $data): void
    {
        Validator::make($data, $this->rules())->validate();
    }

    public function validated(): array
    {
        return Validator::make($this->toArray(), $this->rules())->validated();
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
