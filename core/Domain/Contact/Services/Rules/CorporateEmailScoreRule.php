<?php

namespace App\Core\Domain\Contact\Services\Rules;

use App\Core\Domain\Contact\Entities\Contact;

final class CorporateEmailScoreRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        return $contact->getEmail()->isCorporate() ? 20 : 0;
    }
}
