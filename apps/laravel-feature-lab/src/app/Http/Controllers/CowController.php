<?php

namespace App\Http\Controllers;

use App\Http\Requests\CowStoreRequest;
use App\Http\Requests\CowUpdateRequest;
use App\Http\Resources\CowResource;
use App\Models\Cow;
use Illuminate\Http\Request;

class CowController extends Controller
{
    /**
     * Display a listing of cows, with optional search.
     */
    public function index(Request $request)
    {
        $query = Cow::query();

        if ($q = $request->query('q')) {
            // Simple DB-backed search. Replace with Scout/Meili for production search.
            $query->where('name', 'ilike', "%{$q}%")
                ->orWhere('tag_number', 'ilike', "%{$q}%")
                ->orWhere('breed', 'ilike', "%{$q}%");
        }

        $perPage = (int) $request->query('per_page', 15);
        $cows = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return CowResource::collection($cows)->additional([
            'meta' => [
                'search' => $q ?? null,
            ],
        ]);
    }

    /**
     * Store a newly created cow in storage.
     */
    public function store(CowStoreRequest $request)
    {
        $data = $request->validated();
        $cow = Cow::create($data);

        // Example: dispatch a job or send notification here
        // dispatch(new SomeJob($cow));

        return new CowResource($cow);
    }

    /**
     * Display the specified cow.
     */
    public function show(Cow $cow)
    {
        return new CowResource($cow);
    }

    /**
     * Update the specified cow in storage.
     */
    public function update(CowUpdateRequest $request, Cow $cow)
    {
        $cow->update($request->validated());

        return new CowResource($cow);
    }

    /**
     * Remove the specified cow from storage.
     */
    public function destroy(Cow $cow)
    {
        $cow->delete();

        return response()->noContent();
    }
}
