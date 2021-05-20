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
        return  $this->model->paginate(Paginate::PAGINATE);
    }

    public function findUserInTeam($user_id,$team_id)
    {
        return $this->model->where('user_id','=',$user_id)->where('team_id','=',$team_id)->first();
    }
    public function changeAdmin($id,$role_id)
    {
        return $this->model->where('id','=',$id)->update(['role'=>$role_id]);
    }
}
