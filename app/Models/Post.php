<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wp_id',
        'title',
        'content',
        'status',
        'priority',
        'wp_created_at',
        'wp_modified_at'
    ];

    protected $casts = [
        'priority' => 'integer',
        'wp_created_at' => 'datetime',
        'wp_modified_at' => 'datetime',
    ];

    // Scopes
    public function scopeOrderedByPriority($query)
    {
        return $query->orderByDesc('priority')->orderByDesc('wp_created_at');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'publish');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>', 50);
    }

    // Accessors
    public function getExcerptAttribute($length = 150)
    {
        if (empty($this->content)) {
            return 'No content available';
        }

        $cleanContent = strip_tags($this->content);
        return strlen($cleanContent) > $length
            ? substr($cleanContent, 0, $length) . '...'
            : $cleanContent;
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function getFormattedCreatedAtAttribute()
    {
        if ($this->wp_created_at) {
            return $this->wp_created_at->format('M j, Y g:i A');
        }
        return $this->created_at->format('M j, Y g:i A');
    }

    // Methods
    public function isPublished()
    {
        return $this->status === 'publish';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPrivate()
    {
        return $this->status === 'private';
    }

    public function hasHighPriority()
    {
        return $this->priority > 50;
    }
}
