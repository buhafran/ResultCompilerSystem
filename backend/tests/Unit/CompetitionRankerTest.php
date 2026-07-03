<?php
namespace Tests\Unit;
use App\Support\CompetitionRanker;
use PHPUnit\Framework\TestCase;
class CompetitionRankerTest extends TestCase
{
 public function test_competition_ranking_handles_ties():void{$this->assertSame(['a'=>1,'b'=>2,'c'=>2,'d'=>4],CompetitionRanker::rank(['a'=>100,'b'=>90,'c'=>90,'d'=>80]));}
}
