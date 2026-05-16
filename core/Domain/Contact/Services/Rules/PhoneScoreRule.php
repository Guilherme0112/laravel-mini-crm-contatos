<?php

namespace App\Core\Domain\Contact\Services\Rules;

use App\Core\Domain\Contact\Entities\Contact;

final class PhoneScoreRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        if ($contact->getPhone()->isSaoPaulo()) {
            return 20;
        }

        return $contact->getPhone()->ddd() !== null ? 10 : 0;
    }
}
