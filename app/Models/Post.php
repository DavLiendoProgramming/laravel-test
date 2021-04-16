<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $dates = ['deleted_at'];
    protected $softDelete = true;
    protected $fillable = [
        'title', 'body','published_at','slug'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
