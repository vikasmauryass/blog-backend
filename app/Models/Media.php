<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'alt_text',
        'caption',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
