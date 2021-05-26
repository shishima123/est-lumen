<?php

namespace App\Repositories;

use App\Repositories\RepositoryAbstract;
use App\Models\User;
use App\Enum\Paginate;

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
    public function getData($search, $perPage)
    {
        $model = $this->model;
        if ($search != "") {
            $model = $model->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('email', 'LIKE', '%' . $search . '%');
        }

        if (is_numeric($perPage)) {
            return $model->with('role')->paginate($perPage);
        }

        return $model->with('role')->paginate(Paginate::PAGINATE);
    }
}
