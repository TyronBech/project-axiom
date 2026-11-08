<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentIndexRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Requests\DepartmentRequest;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DepartmentIndexRequest $request)
    {
        $validated = $request->validated();
        $query = Department::query();

        if (isset($validated['code'])) {
            $query->where('code', $validated['code']);
        }

        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request)
    {
        $validated = $request->validated();
        $department = Department::create($validated);

        return response()->json($department, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return response()->json($department);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
