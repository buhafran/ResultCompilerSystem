<?php
namespace Tests\Unit;
use App\Support\GradeScale;
use PHPUnit\Framework\TestCase;
class GradeScaleTest extends TestCase
{
 public function test_default_boundaries_are_consistent():void{$scale=GradeScale::from([['grade'=>'A','min'=>70,'remark'=>'Excellent'],['grade'=>'B','min'=>60,'remark'=>'Very Good'],['grade'=>'C','min'=>50,'remark'=>'Good'],['grade'=>'D','min'=>45,'remark'=>'Fair'],['grade'=>'E','min'=>40,'remark'=>'Pass'],['grade'=>'F','min'=>0,'remark'=>'Needs Improvement']]);$this->assertSame('A',$scale->evaluate(70)['grade']);$this->assertSame('D',$scale->evaluate(45)['grade']);$this->assertSame('F',$scale->evaluate(39.99)['grade']);}
}
