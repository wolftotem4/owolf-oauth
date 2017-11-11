<?php

namespace OWolf\OAuth;

use Prettus\Repository\Eloquent\BaseRepository;

class OAuthUserCredentialsRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return OAuthUserCredentials::class;
    }

    /**
     * @param  string  $name
     * @return $this
     */
    public function provider($name)
    {
        $this->model = $this->model->where('name', $name);
        return $this;
    }

    /**
     * @param  string  $ownerId
     * @return $this
     */
    public function owner($ownerId)
    {
        $this->model = $this->model->where('owner_id', $ownerId);
        return $this;
    }

    /**
     * @param  string  $userId
     * @return $this
     */
    public function user($userId)
    {
        $this->model = $this->model->where('user_id', $userId);
        return $this;
    }
}