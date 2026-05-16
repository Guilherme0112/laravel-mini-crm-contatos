<?php

namespace App\Core\Domain\Contact\Services\Rules;

use App\Core\Domain\Contact\Entities\Contact;

final class BrazilianEmailScoreRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        return $contact->getEmail()->isBrazilian() ? 10 : 0;
    }
}
