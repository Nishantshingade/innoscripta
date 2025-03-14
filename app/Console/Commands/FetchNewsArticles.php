<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from news APIs and update the local database';

    /**
     * Execute the console command.
     */
    public function handle(){
        try {
            $bearerToken = env('API_BEARER_TOKEN');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,  // Add Authorization header with Bearer token
                'Accept' => 'application/json',
            ])->post(route('getAll'), ['source' => 'nytimes']);
            if ($response->successful()) {
                $this->info('Articles fetched successfully.');
            } else {
                $this->error('Failed to fetch articles. Status: ' . $response->status());
                $this->error('Response Body: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error while fetching articles: ' . $e->getMessage());
        }
    }
}
