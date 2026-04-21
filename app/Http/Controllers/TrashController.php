<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrashController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!in_array($user->role, [Roles::INSTRUCTOR, Roles::ADMIN])) {
            abort(403, 'Unauthorized access.');
        }

        return view('trash.index');
    }
}
