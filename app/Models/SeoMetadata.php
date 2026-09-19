<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    use HasFactory;

    protected $table = 'seo_metadata';

    protected $fillable = [
        'post_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
