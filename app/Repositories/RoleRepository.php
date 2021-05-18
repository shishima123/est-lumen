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

    // Get all data
    public function getData()
    {
        return  $this->model->paginate(Paginate::PAGINATE);
    }

    public function search($search)
    {
        if ($search != "") {
            return  $this->model->where('name', 'LIKE', '%' . $search . '%')->get();
        }
        return [];
    }
}
