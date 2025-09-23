<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wp_id', 'title', 'content', 'status', 'priority'
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function scopeOrderedByPriority($q)
    {
        return $q->orderByDesc('priority')->orderByDesc('created_at');
    }
}
