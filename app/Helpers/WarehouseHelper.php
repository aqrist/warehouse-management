<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseHelper
{
    /**
     * Get warehouses accessible by the user
     *
     * @param  User  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAccessibleWarehouses(User $user = null)
    {
        $user = $user ?: auth()->user();
        
        if ($user->hasRole('super-admin')) {
            return Warehouse::where('is_active', true)->get();
        }
        
        $branchIds = $user->branches->pluck('id')->toArray();
        
        return Warehouse::whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->get();
    }
    
    /**
     * Get branches accessible by the user
     *
     * @param  User  $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAccessibleBranches(User $user = null)
    {
        $user = $user ?: auth()->user();
        
        if ($user->hasRole('super-admin')) {
            return Branch::where('is_active', true)->get();
        }
        
        return $user->branches()->where('is_active', true)->get();
    }
    
    /**
     * Check if user can access a specific branch
     *
     * @param  int  $branchId
     * @param  User  $user
     * @return bool
     */
    public static function canAccessBranch($branchId, User $user = null)
    {
        $user = $user ?: auth()->user();
        
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->branches()->where('branches.id', $branchId)->exists();
    }
    
    /**
     * Check if user can access a specific warehouse
     *
     * @param  int  $warehouseId
     * @param  User  $user
     * @return bool
     */
    public static function canAccessWarehouse($warehouseId, User $user = null)
    {
        $user = $user ?: auth()->user();
        
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->branches()
            ->whereHas('warehouses', function ($query) use ($warehouseId) {
                $query->where('warehouses.id', $warehouseId);
            })
            ->exists();
    }

    /**
     * Generate a unique reference number
     *
     * @param  string  $prefix
     * @param  string  $table
     * @param  string  $field
     * @return string
     */
    public static function generateReferenceNumber($prefix, $table, $field = 'reference_no')
    {
        $date = date('Ymd');
        $i = 1;
        
        do {
            $refNo = $prefix . $date . str_pad($i, 4, '0', STR_PAD_LEFT);
            $exists = DB::table($table)->where($field, $refNo)->exists();
            $i++;
        } while ($exists);
        
        return $refNo;
    }
}