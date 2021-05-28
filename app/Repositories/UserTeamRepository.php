<?php

namespace App\Repositories;

use App\Repositories\RepositoryAbstract;
use App\Models\UserTeam;
use App\Enum\Paginate;
use App\Enum\RoleUserTeam;

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
    public function getData($perPage)
    {
        if (is_numeric($perPage)) {
            return $this->model->paginate($perPage);
        }

        return  $this->model->paginate(Paginate::PAGINATE);
    }

    public function findUserInTeam($user_id, $team_id)
    {
        return $this->model->where('user_id', $user_id)->where('team_id', $team_id)->first();
    }
    public function changeAdmin($id, $role_id)
    {
        return $this->model->where('id', $id)->update(['role' => $role_id]);
    }

    public function checkPermission($user_id, $team_id)
    {
        $userInTeam =  $this->findUserInTeam($user_id, $team_id);
        $msg = '';
        if ($userInTeam != null) {
            if ($userInTeam->isMemberRole()) {
                $msg = "You don't have permission for that";
            }
        }
        else{
            $msg = "You are not in team";
        }
        return $msg;
    }

    public function deleteMultiple($arrayId)
    {
        return $this->model->whereIn('user_id', $arrayId)->delete();
    }
}
