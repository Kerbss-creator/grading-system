<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.student-management');
    }

    public function show($id)
    {
       
        // para makita ang detalye ng isang student.
    }

    public function create()
    {
        return view('admin.students-create');
    }

    public function store(Request $request)
    {
       
        // para mag-save ng bagong student.
    }

    public function edit($id)
    {
        
        // para i-edit ang student record.
    }

    public function update(Request $request, $id)
    {
        
        // para i-update ang student record.
    }

    public function destroy($id)
    {
        
        //  mag-delete ng student record.
    }
}
