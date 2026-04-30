<?php
namespace App\Tests\Service;

use App\Entity\DecisionFinanciere;
use App\Entity\EvaluationRisque;
use App\Service\DecisionFinanciereValidator;
use PHPUnit\Framework\TestCase;

class DecisionFinanciereValidatorTest extends TestCase
{
    private DecisionFinanciereValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DecisionFinanciereValidator();
    }

    public function testStatutApprouveAvecBonScore(): void
    {
        $evaluation = $this->createMock(EvaluationRisque::class);
        $evaluation->method('getScoreGlobal')->willReturn(75);

        $decision = $this->createMock(DecisionFinanciere::class);
        $decision->method('getStatut')->willReturn('approuve');
        $decision->method('getEvaluation')->willReturn($evaluation);

        $this->assertTrue($this->validator->validate($decision));
    }

    public function testStatutApprouveAvecMauvaisScore(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $evaluation = $this->createMock(EvaluationRisque::class);
        $evaluation->method('getScoreGlobal')->willReturn(30);

        $decision = $this->createMock(DecisionFinanciere::class);
        $decision->method('getStatut')->willReturn('approuve');
        $decision->method('getEvaluation')->willReturn($evaluation);

        $this->validator->validate($decision);
    }

    public function testStatutRefuseSansJustification(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $decision = $this->createMock(DecisionFinanciere::class);
        $decision->method('getStatut')->willReturn('refuse');
        $decision->method('getJustification')->willReturn('');

        $this->validator->validate($decision);
    }

    public function testStatutRefuseAvecJustification(): void
    {
        $decision = $this->createMock(DecisionFinanciere::class);
        $decision->method('getStatut')->willReturn('refuse');
        $decision->method('getJustification')->willReturn('Dossier incomplet');

        $this->assertTrue($this->validator->validate($decision));
    }

    public function testStatutEnAttente(): void
    {
        $decision = $this->createMock(DecisionFinanciere::class);
        $decision->method('getStatut')->willReturn('en_attente');

        $this->assertTrue($this->validator->validate($decision));
    }

    public function testStatutInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $decision = $this->createMock(DecisionFinanciere::class);
        $decision->method('getStatut')->willReturn('inconnu');

        $this->validator->validate($decision);
    }
}
