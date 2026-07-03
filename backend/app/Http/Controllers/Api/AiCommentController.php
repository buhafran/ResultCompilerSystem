<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ResultSummary;
use App\Models\School;
use App\Services\AiCommentService;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class AiCommentController extends Controller
{
 public function generate(Request $request,School $school,ResultSummary $summary,AiCommentService $service,AuditService $audit):JsonResponse
 {
  abort_unless($summary->school_id===$school->id,404);abort_unless($request->user()->isSchoolManager($school),403);
  $comments=$service->generate($summary);$before=$summary->only(['teacher_comment','principal_comment','ai_comment_generated']);$summary->update(['teacher_comment'=>$comments['teacher_comment'],'principal_comment'=>$comments['principal_comment'],'ai_comment_generated'=>true]);$audit->record('result.ai_comment_generated',$summary,$before,$summary->fresh()->only(['teacher_comment','principal_comment','ai_comment_generated']),$school->id);
  return response()->json(['message'=>'Draft comments generated. Review before release.','source'=>$comments['source'],'data'=>$comments]);
 }
}
