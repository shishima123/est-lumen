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

    // Get all and search Data
    public function getData($search)
    {
        $model = $this->model;
        if ($search != "") {
            $model = $model->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('email', 'LIKE', '%' . $search . '%');
        }
        return $model->paginate(Paginate::PAGINATE);
    }
}
