<?php

namespace App\Core\Domain\Contact\Services;

use App\Core\Domain\Contact\Entities\Contact;
use App\Core\Domain\Contact\Services\Rules\ScoreRule;

final class ScoreCalculator
{
    /**
     * @param ScoreRule[] $rules
     */
    public function __construct(private array $rules)
    {
    }

    public function calculate(Contact $contact): int
    {
        return array_reduce(
            $this->rules,
            fn (int $carry, ScoreRule $rule) => $carry + $rule->score($contact),
            0
        );
    }
}
