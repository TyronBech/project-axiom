<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Collection;

class DepartmentService
{
    public function list(array $filters): Collection
    {
        $departments = Department::query();

        if (isset($filters['code'])) {
            $departments->where('code', $filters['code']);
        }

        return $departments->get();
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);

        return $department;
    }

    public function delete(Department $department): bool
    {
        if ($department->users()->exists()) {
            return false;
        }

        $department->delete();

        return true;
    }
}
