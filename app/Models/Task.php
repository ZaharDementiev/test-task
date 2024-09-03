<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
