<?php

namespace App\Http\Controllers;

use App\Http\Requests\CowStoreRequest;
use App\Http\Requests\CowUpdateRequest;
use App\Http\Resources\CowResource;
use App\Models\Cow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CowController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of cows, with optional search.
     * 
     * Demonstrates: Policy authorization (viewAny)
     */
    public function index(Request $request)
    {
        // Authorization check: uses CowPolicy::viewAny()
        $this->authorize('viewAny', Cow::class);

        $query = Cow::query();

        if ($q = $request->query('q')) {
            // Use Scout for full-text search
            $perPage = (int) $request->query('per_page', 15);
            $cows = Cow::search($q)->paginate($perPage);
            
            return CowResource::collection($cows)->additional([
                'meta' => [
                    'search' => $q,
                ],
            ]);
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
     * 
     * Demonstrates: Policy authorization (create)
     */
    public function store(CowStoreRequest $request)
    {
        // Authorization check: uses CowPolicy::create()
        $this->authorize('create', Cow::class);

        $data = $request->validated();
        $cow = Cow::create($data);

        // Example: dispatch a job or send notification here
        // dispatch(new SomeJob($cow));

        return new CowResource($cow);
    }

    /**
     * Display the specified cow.
     * 
     * Demonstrates: Policy authorization (view)
     */
    public function show(Cow $cow)
    {
        // Authorization check: uses CowPolicy::view()
        $this->authorize('view', $cow);

        return new CowResource($cow);
    }

    /**
     * Update the specified cow in storage.
     * 
     * Demonstrates: Policy authorization (update) - only admins allowed
     */
    public function update(CowUpdateRequest $request, Cow $cow)
    {
        // Authorization check: uses CowPolicy::update()
        // Only admins can update (will return 403 for non-admins)
        $this->authorize('update', $cow);

        $cow->update($request->validated());

        return new CowResource($cow);
    }

    /**
     * Remove the specified cow from storage.
     * 
     * Demonstrates: Policy authorization (delete) - only admins allowed
     */
    public function destroy(Cow $cow)
    {
        // Authorization check: uses CowPolicy::delete()
        // Only admins can delete (will return 403 for non-admins)
        $this->authorize('delete', $cow);

        $cow->delete();

        return response()->noContent();
    }
}
