<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateFilmAPIRequest;
use App\Http\Requests\API\UpdateFilmAPIRequest;
use App\Models\Film;
use App\Repositories\FilmRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class FilmAPIController
 */
class FilmAPIController extends AppBaseController
{
    private FilmRepository $filmRepository;

    public function __construct(FilmRepository $filmRepo)
    {
        $this->filmRepository = $filmRepo;
    }

    /**
     * Display a listing of the Films.
     * GET|HEAD /films
     */
    public function index(Request $request): JsonResponse
    {
        $films = $this->filmRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($films->toArray(), 'Films retrieved successfully');
    }

    /**
     * Store a newly created Film in storage.
     * POST /films
     */
    public function store(CreateFilmAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $film = $this->filmRepository->create($input);

        return $this->sendResponse($film->toArray(), 'Film saved successfully');
    }

    /**
     * Display the specified Film.
     * GET|HEAD /films/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var Film $film */
        $film = $this->filmRepository->find($id);

        if (empty($film)) {
            return $this->sendError('Film not found');
        }

        return $this->sendResponse($film->toArray(), 'Film retrieved successfully');
    }

    /**
     * Update the specified Film in storage.
     * PUT/PATCH /films/{id}
     */
    public function update($id, UpdateFilmAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Film $film */
        $film = $this->filmRepository->find($id);

        if (empty($film)) {
            return $this->sendError('Film not found');
        }

        $film = $this->filmRepository->update($input, $id);

        return $this->sendResponse($film->toArray(), 'Film updated successfully');
    }

    /**
     * Remove the specified Film from storage.
     * DELETE /films/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var Film $film */
        $film = $this->filmRepository->find($id);

        if (empty($film)) {
            return $this->sendError('Film not found');
        }

        $film->delete();

        return $this->sendSuccess('Film deleted successfully');
    }
}
