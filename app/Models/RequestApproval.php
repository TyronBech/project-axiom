<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['status', 'remarks', 'approver_id', 'request_id'])]
class RequestApproval extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id', 'id');
    }
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }
}
