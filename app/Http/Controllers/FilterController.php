<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function index(){


        $formConfig = array (
           // 'formName' => 'User Registration',
            'fields' => 
            array (
              0 => 
              array (
                'type' => 'text',
                'name' => 'firstName',
                'label' => 'First Name',
                'placeholder' => 'Enter your first name',
                'validation' => 
                array (
                  'required' => true,
                  'minLength' => 3,
                ),
              ),
              1 => 
              array (
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email',
                'placeholder' => 'Enter your email',
                'validation' => 
                array (
                  'required' => true,
                  'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,4}$',
                ),
              ),
              2 => 
              array (
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Last Name',
                'placeholder' => 'Enter your Last Name',
                'validation' => 
                array (
                  'required' => true,
                  'minLength' => 8,
                ),
              ),
            ),
        );


        $response = ['status' => true, 'data' => $formConfig];

        return response()->json($response);
        
        
    }
}
