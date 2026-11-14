<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Department;

abstract class TestCase extends BaseTestCase
{
    protected User $user;
    protected Department $department;
}
