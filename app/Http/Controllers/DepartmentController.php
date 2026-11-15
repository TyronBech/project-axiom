<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentIndexRequest;
use App\Http\Requests\DepartmentRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DepartmentIndexRequest $request, DepartmentService $service)
    {
        $validated = $request->validated();

        return $service->list($validated);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request, DepartmentService $service)
    {
        $validated = $request->validated();
        $department = $service->create($validated);

        return response()->json($department, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        Gate::authorize('view', $department);

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
    public function update(DepartmentUpdateRequest $request, Department $department, DepartmentService $service)
    {
        $validated = $request->validated();
        $department = $service->update($department, $validated);

        return response()->json($department);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department, DepartmentService $service)
    {
        Gate::authorize('delete', $department);

        $deleted = $service->delete($department);

        return $deleted
            ? response()->noContent()
            : response()->json([
                'message' => 'Cannot delete department with associated users.',
            ], 409);
    }
}
