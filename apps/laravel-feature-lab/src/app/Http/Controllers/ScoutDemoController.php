<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Scout demo controller demonstrating various search features
 * 
 * Scout provides full-text search capabilities using Meilisearch (free and open-source)
 */
class ScoutDemoController extends Controller
{
    /**
     * Basic search
     * Demonstrates: Simple search query
     */
    public function basicSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'model' => 'required|in:cows,users',
        ]);

        $query = $request->input('q');
        $model = $request->input('model');

        $results = match ($model) {
            'cows' => Cow::search($query)->get(),
            'users' => User::search($query)->get(),
        };

        return response()->json([
            'query' => $query,
            'model' => $model,
            'results' => $results,
            'count' => $results->count(),
        ]);
    }

    /**
     * Paginated search
     * Demonstrates: Search with pagination
     */
    public function paginatedSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'model' => 'required|in:cows,users',
            'per_page' => 'integer|min:1|max:100',
        ]);

        $query = $request->input('q');
        $model = $request->input('model');
        $perPage = $request->input('per_page', 15);

        $results = match ($model) {
            'cows' => Cow::search($query)->paginate($perPage),
            'users' => User::search($query)->paginate($perPage),
        };

        return response()->json([
            'query' => $query,
            'model' => $model,
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
        ]);
    }

    /**
     * Search with filters
     * Demonstrates: Filtered search results
     */
    public function filteredSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'model' => 'required|in:cows,users',
        ]);

        $query = $request->input('q');
        $model = $request->input('model');

        // Note: Meilisearch filtering requires proper index configuration
        // This is a basic example
        $results = match ($model) {
            'cows' => Cow::search($query)->get(),
            'users' => User::search($query)->get(),
        };

        return response()->json([
            'query' => $query,
            'model' => $model,
            'results' => $results,
            'count' => $results->count(),
            'note' => 'Filtering requires index configuration in Meilisearch',
        ]);
    }

    /**
     * Search within specific fields
     * Demonstrates: Field-specific search
     */
    public function fieldSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'model' => 'required|in:cows,users',
            'field' => 'string',
        ]);

        $query = $request->input('q');
        $model = $request->input('model');
        $field = $request->input('field');

        // Basic search (field-specific search requires index configuration)
        $results = match ($model) {
            'cows' => Cow::search($query)->get(),
            'users' => User::search($query)->get(),
        };

        return response()->json([
            'query' => $query,
            'model' => $model,
            'field' => $field,
            'results' => $results,
            'count' => $results->count(),
            'note' => 'Field-specific search requires index configuration',
        ]);
    }

    /**
     * Search with ordering
     * Demonstrates: Ordered search results
     */
    public function orderedSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'model' => 'required|in:cows,users',
            'order_by' => 'string',
            'order_direction' => 'in:asc,desc',
        ]);

        $query = $request->input('q');
        $model = $request->input('model');
        $orderBy = $request->input('order_by', 'id');
        $orderDirection = $request->input('order_direction', 'asc');

        $results = match ($model) {
            'cows' => Cow::search($query)->get()->sortBy($orderBy, SORT_REGULAR, $orderDirection === 'desc'),
            'users' => User::search($query)->get()->sortBy($orderBy, SORT_REGULAR, $orderDirection === 'desc'),
        };

        return response()->json([
            'query' => $query,
            'model' => $model,
            'order_by' => $orderBy,
            'order_direction' => $orderDirection,
            'results' => $results->values(),
            'count' => $results->count(),
        ]);
    }

    /**
     * Import all models to search index
     * Demonstrates: Bulk indexing
     */
    public function importAll(Request $request): JsonResponse
    {
        $request->validate([
            'model' => 'required|in:cows,users',
        ]);

        $model = $request->input('model');

        try {
            match ($model) {
                'cows' => Cow::makeAllSearchable(),
                'users' => User::makeAllSearchable(),
            };

            return response()->json([
                'message' => "All {$model} imported to search index",
                'model' => $model,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove all models from search index
     * Demonstrates: Clearing search index
     */
    public function removeAll(Request $request): JsonResponse
    {
        $request->validate([
            'model' => 'required|in:cows,users',
        ]);

        $model = $request->input('model');

        try {
            match ($model) {
                'cows' => Cow::removeAllFromSearch(),
                'users' => User::removeAllFromSearch(),
            };

            return response()->json([
                'message' => "All {$model} removed from search index",
                'model' => $model,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search statistics
     * Demonstrates: Search index information
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'message' => 'Search statistics',
            'driver' => config('scout.driver'),
            'prefix' => config('scout.prefix'),
            'queue' => config('scout.queue'),
            'meilisearch_host' => config('scout.meilisearch.host'),
            'note' => 'Use Meilisearch dashboard for detailed statistics',
        ]);
    }
}

