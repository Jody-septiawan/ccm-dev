<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Facades\DB;
use App\Models\Rating;

class RatingRepository
{
    protected $model;

    public function __construct(Rating $model)
    {
        $this->model = $model;
    }

    /**
     * Get rating by value
     *
     * @param integer $value
     * 
     * @return void
     */
    public function getByValue(int $value)
    {
        return $this->model->where('value', $value)->first();
    }
}