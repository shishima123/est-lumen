<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enum\Verify;
use App\Repositories\RoleRepository;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $roleRepo;

    public function __construct( RoleRepository $_roleRepository) {
        $this->roleRepo = $_roleRepository;
    }
    public function run()
    {
        User::create([
            'name' => 'nga123',
            'email' => 'sample123@yopmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $this->roleRepo->findByField('name','admin')->first()->id,
            'is_verified' =>Verify::VERIFY,
            'verification_code'=>''
        ]);
    }
}
