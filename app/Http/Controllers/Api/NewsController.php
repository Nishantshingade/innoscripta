<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\CreateModelTrait;
use App\Traits\HttpTrait;
use App\Http\Requests\createGetNewsRequest;
use Illuminate\Http\jsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use App\Models\News;
use App\Models\Preference;
use App\Models\PreferenceSource;
use App\Models\PreferenceCategory;
use App\Models\PreferenceAuthor;

class NewsController extends Controller
{
    use CreateModelTrait, HttpTrait;

    public function getArticles(createGetNewsRequest $request) :jsonResponse{
        try{
            $data = array();
            switch ($request->source) {
                case 'newsorg':
                    $key = config('services.keys.newsorg');
                    $elements = array();
                    $url = "https://newsapi.org/v2/everything?q=health&apiKey=".$key;
                    break;
                
                case 'newsapi':
                    $key = config('services.keys.newsapi');
                    $elements = array('source','categories','title','url','authors','dateTimePub');
                    //$url = "https://eventregistry.org/api/v1/article/getArticles?query=%7B%22%24query%22%3A%7B%22%24and%22%3A%5B%7B%22conceptUri%22%3A%22http%3A%2F%2Fen.wikipedia.org%2Fwiki%2FAstrology%22%7D%2C%7B%22dateStart%22%3A%222025-02-01%22%2C%22dateEnd%22%3A%222025-03-10%22%2C%22lang%22%3A%22eng%22%7D%5D%7D%7D&resultType=articles&articlesSortBy=date&includeArticleCategories=true&apiKey=".$key;
                    $url = 'https://eventregistry.org/api/v1/article/getArticles?query=%7B%22%24query%22%3A%7B%22%24and%22%3A%5B%7B%22categoryUri%22%3A%22dmoz%2FSports%22%7D%2C%7B%22dateStart%22%3A%222025-02-01%22%2C%22dateEnd%22%3A%222025-03-11%22%2C%22lang%22%3A%22eng%22%7D%5D%7D%7D&resultType=articles&articlesSortBy=date&includeArticleCategories=true&apiKey='.$key;
                    break;
                
                case 'guardian':
                    $key = config('services.keys.guardian');
                    $url = "https://content.guardianapis.com/search?q=cricket&api-key=".$key;
                    break;

                case 'nytimes':
                    $key = config('services.keys.nytimes');
                    //$url = "https://api.nytimes.com/svc/news/v3/content/all/all.json?api-key=".$key;
                    $url = "https://api.nytimes.com/svc/topstories/v2/arts.json?api-key=".$key;
                    break;
                
                default:
                    // Code to execute if $variable does not match any case
                    break;
            }
            $res = json_decode($this->http($url),true);
            if($request->source == 'newsapi'){
                if(isset($res['articles']['results']) && count($res['articles']['results'])>0){
                    foreach($res['articles']['results'] as $article){ 
                        $data['type'] = 'newsapi';
                        $data['source'] = $article['source']['title'] ?? '';
                        $data['category'] =  $article['categories'][0]['label'] ?? '';
                        $data['title'] = $article['title'] ?? 'NA';
                        $data['url'] = $article['url'] ?? '';
                        $data['author'] = $article['authors'][0]['name'] ?? '';
                        $data['published_at'] = $article['dateTimePub'] ?? '';
                        $this->createModelRecord(News::class,$data);
                    }
                }
            }
            if($request->source == 'newsorg'){
                if(isset($res['articles']) && count($res['articles'])>0){
                    foreach($res['articles'] as $article){ 
                        $data['type'] = 'newsorg';
                        $data['source'] = $article['source']['name'] ?? '';
                        $data['category'] =  $article['categories'][0]['label'] ?? 'health';
                        $data['title'] = $article['title'] ?? 'NA';
                        $data['url'] = $article['url'] ?? '';
                        $data['author'] = $article['author'] ?? '';
                        $data['published_at'] = $article['publishedAt'] ?? '';
                        $this->createModelRecord(News::class,$data);
                    }
                }
            }
            if($request->source == 'nytimes'){
                if(isset($res['results']) && count($res['results'])>0){
                    foreach($res['results'] as $article){ 
                        $data['type'] = 'nytimes';
                        $data['source'] = 'nytimes';
                        $data['category'] =  $article['section'] ?? 'arts';
                        $data['title'] = $article['title'] ?? 'NA';
                        $data['url'] = $article['url'] ?? '';
                        $data['author'] = $article['byline'] ?? '';
                        $data['published_at'] = $article['published_date'] ?? '';
                        $this->createModelRecord(News::class,$data);
                    }
                }
            }
            return response()->json(['message' => 'Api fetch Successfull','api' => $request->source,'status'=>200], 200);
        }catch (\Exception $e) {
            \Log::error('API fetch failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred during fetching the records.','error' => $e->getMessage()], 500);
        }
        
    }

    public function fetchArticle($id){
        try{
            $article = News::findOrFail($id);
            return response()->json([
                'message' => 'Article fetched successfully',
                'status' => 200,
                'data' => $article
            ], 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Article not found.','status' => 404], 404);
        }
        
    }
    
    public function list(Request $request){
        try{
            $perPage = (int) $request->get('per_page', 15);
            $perPage = $perPage > 0 ? $perPage : 15;
            $filters = [
                'category' => $request->get('category'),
                'source' => $request->get('source'),
                'title' => $request->get('title'),
                'published_at' => $request->get('published_at')
            ];
            $query = News::query();
            foreach ($filters as $key => $value) {
                if ($value) {
                    if ($key === 'title') {
                        $query->where($key, 'like', '%' . $value . '%');
                    } elseif ($key === 'published_at') {
                        $query->whereDate($key, $value);
                    } else {
                        $query->where($key, $value);
                    }
                }
            }
            $articles = $query->paginate($perPage);
            return response()->json([
                'message' => 'Articles filtered successfully',
                'data' => $articles
            ], 200);
        }catch (AuthenticationException $e) {
            return response()->json(['message' => 'Unauthenticated. Please provide a valid token.'], 401);
        }
        catch (Exception $e) {
            \Log::error('Something went wrong!: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred.','error' => $e->getMessage()], 500);
        }
        
    }
    
    public function setpreference(Request $request){
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'sources' => 'required|array',
            'categories' => 'required|array',
            'authors' => 'required|array',
        ]);
        Preference::where('user_id', $request->user_id)->delete();
        $preference = Preference::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
        ]);

        foreach ($request->sources as $source) {
            PreferenceSource::create([
                'preference_id' => $preference->id,
                'source' => $source,
            ]);
        }

        foreach ($request->categories as $category) {
            PreferenceCategory::create([
                'preference_id' => $preference->id,
                'category' => $category,
            ]);
        }

        foreach ($request->authors as $author) {
            PreferenceAuthor::create([
                'preference_id' => $preference->id,
                'author' => $author,
            ]);
        }

        return response()->json(['message' => 'Preferences saved successfully'], 201);
    }

    public function getpreference($userId){
        $preferences = Preference::with(['sources', 'categories', 'authors'])
            ->where('user_id', $userId)
            ->get();
        $newsQuery = News::query();
        foreach ($preferences as $preference) {
            $newsQuery->Where('type', $preference->type);
            foreach ($preference->sources as $source) {
                $newsQuery->orWhere('source', 'like', '%' . $source->source . '%');
            }
        
            foreach ($preference->categories as $category) {
                $newsQuery->orWhere('category', 'like', '%' . $category->category . '%');
            }
        
            foreach ($preference->authors as $author) {
                $newsQuery->orWhere('author', 'like', '%' . $author->author . '%');
            }
        }
        $filteredNews = $newsQuery->get();
        return response()->json($filteredNews);
    }

}
