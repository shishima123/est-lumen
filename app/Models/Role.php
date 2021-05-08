<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'name',
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
