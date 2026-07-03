<?php
use App\Http\Controllers\Api\AiCommentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeacherResultController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function(){
 Route::post('/login',[AuthController::class,'login'])->middleware('throttle:10,1');
 Route::middleware('auth:sanctum')->group(function(){
  Route::get('/me',[AuthController::class,'me']);Route::post('/logout',[AuthController::class,'logout']);
  Route::prefix('/schools/{school:slug}')->middleware('school.member')->group(function(){
   Route::get('/assignments',[TeacherResultController::class,'assignments'])->middleware('abilities:teacher:read');
   Route::get('/assignments/{assignment}/roster',[TeacherResultController::class,'roster'])->middleware('abilities:teacher:read');
   Route::put('/assignments/{assignment}/score',[TeacherResultController::class,'save'])->middleware(['abilities:scores:write','throttle:120,1']);
   Route::post('/assignments/{assignment}/sync',[TeacherResultController::class,'sync'])->middleware(['abilities:scores:write','throttle:30,1']);
   Route::post('/result-summaries/{summary}/ai-comments',[AiCommentController::class,'generate'])->middleware(['abilities:comments:generate','throttle:20,1']);
  });
 });
});
