<?php

namespace App\Repositories;

use App\Models\Film;
use App\Repositories\BaseRepository;

class FilmRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'titre',
        'synopsis',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Film::class;
    }
}
