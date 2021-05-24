<?php

namespace App\Repositories;

use App\Repositories\RepositoryAbstract;
use App\Models\Role;
use App\Enum\Paginate;

/**
 * Class RoleRepository
 *
 * @package App\Repositories
 */
class RoleRepository extends RepositoryAbstract
{
    /**
     * Get model name
     *
     * @return string
     */
    public function getModel()
    {
        return Role::class;
    }

    // Get all and search Data
    public function getData($search)
    {
        $model = $this->model;
        if ($search != "") {
            $model = $model->where('name', 'LIKE', '%' . $search . '%');
        }
        return $model->paginate(Paginate::PAGINATE);
    }
}
