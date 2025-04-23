<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Branch;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $branchId = $request->route('branch');
        
        // Skip check for super admin
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        // If branchId is a Branch model instance, get its ID
        if ($branchId instanceof Branch) {
            $branchId = $branchId->id;
        }
        
        // Check if branch ID exists in the user's assigned branches
        $hasBranchAccess = $user->branches()->where('branches.id', $branchId)->exists();
        
        if (!$hasBranchAccess) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini');
        }
        
        return $next($request);
    }
}
