<?php

namespace App\Core\Domain\Contact\Services\Rules;

use App\Core\Domain\Contact\Entities\Contact;

interface ScoreRule
{
    public function score(Contact $contact): int;
}
