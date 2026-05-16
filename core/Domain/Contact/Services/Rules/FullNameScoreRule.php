<?php

namespace App\Core\Domain\Contact\Services\Rules;

use App\Core\Domain\Contact\Entities\Contact;

final class FullNameScoreRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        return count(explode(' ', trim($contact->getName()))) > 1 ? 10 : 0;
    }
}
