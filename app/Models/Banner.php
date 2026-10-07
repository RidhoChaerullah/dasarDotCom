<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Banner extends Model
{
    protected $fillable = ['title', 'image_path', 'link_url', 'sort_order', 'is_active'];
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
