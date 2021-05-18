<?php

namespace App\Repositories;

use App\Repositories\RepositoryAbstract;
use App\Models\User;
use App\Enum\Paginate;
use Illuminate\Http\Request;

/**
 * Class UserRepository
 *
 * @package App\Repositories
 */
class UserRepository extends RepositoryAbstract
{
    /**
     * Get model name
     *
     * @return string
     */
    public function getModel()
    {
        return User::class;
    }

    // Get all data
    public function getData()
    {
        return $this->model->paginate(Paginate::PAGINATE);
    }

    public function search($search)
    {

        if ($search != "") {
            return  $this->model->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('email', 'LIKE', '%' . $search . '%')
                ->get();
        }
        return [];
    }
}
