<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\RequestStatus;
use App\Enums\RequestPriority;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['request_number', 'requester_id', 'department_id', 'title', 'description', 'status', 'priority'])]
class Request extends Model
{
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'priority' => RequestPriority::class,
        ];
    }
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    public function approvals(): HasMany
    {
        return $this->hasMany(RequestApproval::class, 'request_id', 'id');
    }
    public function items(): HasMany
    {
        return $this->hasMany(RequestItem::class, 'request_id', 'id');
    }
}
