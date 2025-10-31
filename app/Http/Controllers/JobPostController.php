<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\User;
use App\Notifications\FirstJobPostingNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class JobPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = JobPost::query()
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        if (!$request->has('user_id')) {
            Cache::forget('personio_jobs_cache');
            $externalJobs = $this->getExternalDataSource();
            $allJobs = $data->toArray();
            $data = array_merge($allJobs, $externalJobs);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $data,
            'message' => 'Successfully fetched data.'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $jobPost = JobPost::create($request->only(['title', 'description', 'user_id']));

        if (JobPost::where('user_id', $request->user_id)->count() === 1) {
            $moderator = User::find(1);
            $jobPostCreator = User::find($request->user_id);
            Notification::send($moderator, new FirstJobPostingNotification($jobPostCreator, $jobPost));
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $jobPost->toArray(),
            'message' => 'Successfully saved the data.'
        ]);
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    public function show(JobPost $jobPost)
    {
        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $jobPost->toArray(),
            'message' => 'Successfully fetched the data.'
        ]);
    }

    public function getExternalData($externalDataSourceId)
    {
        $data = [];
        $externalJobs = $this->getExternalDataSource();
        $data = collect($externalJobs)->firstWhere('id', $externalDataSourceId);

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $data,
            'message' => 'Successfully fetched the data.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPost $jobPost)
    {
        $jobPost->update($request->toArray());
        $notification = User::find(1)->notifications()
            ->where('data->job_post_id', $jobPost->id)
            ->whereNull('read_at')
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $jobPost->refresh(),
            'message' => 'Successfully saved the data.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function getExternalDataSource()
    {
        return Cache::remember('personio_jobs_cache', 3 * 60 * 60, function () {
            try {
                $url = 'https://mrge-group-gmbh.jobs.personio.de/xml';
                $response = Http::get($url);
                $xmlContent = $response->body();
            } catch (\Throwable $th) {
                //throw $th;
                $xmlPath = public_path('xml.xml');
                $xmlContent = file_get_contents($xmlPath);
            }

            $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            $jobs = json_decode(json_encode($xml), true)['position'] ?? [];

            return collect($jobs)->map(function ($job) {
                return [
                    'id'              => $job['id'] ?? null,
                    'title'           => $job['name'] ?? '',
                    'description'     => collect($job['jobDescriptions']['jobDescription'] ?? [])
                    ->map(function ($desc) {
                        $name = isset($desc['name']) ? trim((string) $desc['name']) : '';
                        $value = isset($desc['value']) ? trim((string) $desc['value']) : '';

                        return htmlentities("<h3>{$name}</h3>\n{$value}");
                    })
                    ->implode("\n\n"),
                    'created_at'      => $job['createdAt'] ?? '',
                    'status'          => 'external',
                ];
            })->toArray();
        });
    }
}
