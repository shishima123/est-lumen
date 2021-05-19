<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Team extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'teams';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_team');
    }
}
