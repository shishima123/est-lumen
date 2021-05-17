<?php

namespace App\Repositories;

use App\Repositories\RepositoryAbstract;
use App\Models\Team;
use App\Enum\Paginate;

/**
 * Class TeamRepository
 *
 * @package App\Repositories
 */
class TeamRepository extends RepositoryAbstract
{
    /**
     * Get model name
     *
     * @return string
     */
    public function getModel()
    {
        return Team::class;
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
