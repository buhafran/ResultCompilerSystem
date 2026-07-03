<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var School|null $school */
        $school = $request->route('school');
        $user = $request->user();

        abort_unless($school && $user, 401, 'Authentication required.');
        abort_unless($user->is_super_admin || canAccessTenant($school), 403, 'You do not belong to this school.');

        $user->forceFill(['last_school_id' => $school->id])->saveQuietly();
        return $next($request);
    }
}
