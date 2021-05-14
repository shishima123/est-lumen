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

    // Get all data
    public function getData()
    {
        return $this->model->paginate(Paginate::PAGINATE);
    }
}
