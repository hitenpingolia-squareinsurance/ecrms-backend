<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class Test extends Controller
{
    public function index(){

        return User::select('id','name')->with('details:company_id')->get();
    }
}
