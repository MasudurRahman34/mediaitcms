<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'subject', 'message', 'status', 'ip',
    ];

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
