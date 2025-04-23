<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class CheckWarehouseAccess
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
        $warehouseId = $request->route('warehouse');
        
        // Skip check for super admin
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        // If warehouseId is a Warehouse model instance, get its ID
        if ($warehouseId instanceof Warehouse) {
            $warehouseId = $warehouseId->id;
        }
        
        // Check if warehouse is in any of the user's assigned branches
        $hasWarehouseAccess = $user->branches()
            ->whereHas('warehouses', function ($query) use ($warehouseId) {
                $query->where('warehouses.id', $warehouseId);
            })
            ->exists();
        
        if (!$hasWarehouseAccess) {
            abort(403, 'Anda tidak memiliki akses ke gudang ini');
        }
        
        return $next($request);
    }
}