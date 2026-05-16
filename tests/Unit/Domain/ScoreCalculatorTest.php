<?php

namespace Tests\Unit\Domain;

use App\Core\Domain\Contact\Entities\Contact;
use App\Core\Domain\Contact\Services\ScoreCalculator;
use App\Core\Domain\Contact\Services\Rules\BrazilianEmailScoreRule;
use App\Core\Domain\Contact\Services\Rules\CorporateEmailScoreRule;
use App\Core\Domain\Contact\Services\Rules\FullNameScoreRule;
use App\Core\Domain\Contact\Services\Rules\PhoneScoreRule;
use PHPUnit\Framework\TestCase;

class ScoreCalculatorTest extends TestCase
{
    public function test_should_calculate_score_based_on_rules()
    {
        $contact = Contact::create('Guilherme Silva', 'guilherme@empresa.com.br', '11987654321');

        $calculator = new ScoreCalculator([
            new CorporateEmailScoreRule(),
            new BrazilianEmailScoreRule(),
            new FullNameScoreRule(),
            new PhoneScoreRule(),
        ]);

        $score = $calculator->calculate($contact);

        $this->assertEquals(60, $score);
    }
}
