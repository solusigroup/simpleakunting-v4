<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display the role and permission matrix.
     */
    public function index()
    {
        $roles = ['Administrator', 'Manajer', 'Operator', 'Peninjau'];
        $matrix = PermissionHelper::getPermissionMatrix();
        
        return view('roles.index', compact('roles', 'matrix'));
    }
}
