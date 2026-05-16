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

    public function test_should_return_zero_when_no_rules_are_provided()
    {
        $contact = Contact::create('Guilherme', 'gui@gmail.com', '11987654321');
    
        $calculator = new ScoreCalculator([]);
        
        $score = $calculator->calculate($contact);
        
        $this->assertEquals(0, $score);
    }

    public function test_should_calculate_score_for_generic_email_and_single_name()
    {
        $contact = Contact::create('Guilherme', 'gui@gmail.com', '11987654321');

        $calculator = new ScoreCalculator([
            new CorporateEmailScoreRule(),
            new BrazilianEmailScoreRule(),
            new FullNameScoreRule(),
            new PhoneScoreRule(),
        ]);

        $score = $calculator->calculate($contact);
        $this->assertEquals(20, $score); 
    }

    public function test_should_calculate_score_for_corporate_but_not_brazilian_email()
    {
        $contact = Contact::create('Guilherme Silva', 'guilherme@startup.io', '11987654321');

        $calculator = new ScoreCalculator([
            new CorporateEmailScoreRule(),
            new BrazilianEmailScoreRule(),
            new FullNameScoreRule(),
            new PhoneScoreRule(),
        ]);

        $score = $calculator->calculate($contact);
        $this->assertEquals(50, $score); 
    }

    public function test_should_calculate_score_for_brazilian_generic_email_with_full_name()
    {
        $contact = Contact::create('Guilherme Silva', 'guilherme@hotmail.com.br', '11987654321');

        $calculator = new ScoreCalculator([
            new CorporateEmailScoreRule(),
            new BrazilianEmailScoreRule(),
            new FullNameScoreRule(),
            new PhoneScoreRule(),
        ]);

        $score = $calculator->calculate($contact);
        $this->assertEquals(60, $score); 
    }

    public function test_should_calculate_score_using_only_specific_rules()
    {
        $contact = Contact::create('Guilherme Silva', 'guilherme@empresa.com.br', '11987654321');
        $calculator = new ScoreCalculator([
            new FullNameScoreRule(),
            new BrazilianEmailScoreRule(),
        ]);

        $score = $calculator->calculate($contact);
        $this->assertEquals(20, $score); 
    }
}
