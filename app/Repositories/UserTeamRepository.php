<?php

namespace App\Repositories;

use App\Repositories\RepositoryAbstract;
use App\Models\UserTeam;
use App\Enum\Paginate;

/**
 * Class UserTeamRepository
 *
 * @package App\Repositories
 */
class UserTeamRepository extends RepositoryAbstract
{
    /**
     * Get model name
     *
     * @return string
     */
    public function getModel()
    {
        return UserTeam::class;
    }

    // Get all data
    public function getData()
    {
        $userTeams = $this->model->paginate(Paginate::PAGINATE);
        return $userTeams;
    }
}
