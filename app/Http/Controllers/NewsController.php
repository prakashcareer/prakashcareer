<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class NewsController extends Controller
{
    protected $base_url = 'https://timesofindia.indiatimes.com/rssfeeds/-2128838597.cms?feedtype=json';

    public function index(Request $request)
    {
        $client = new Client();

        try {
            // Fetch data from Times of India RSS feed
            $response = $client->get($this->base_url);
            $data = json_decode($response->getBody()->getContents());

            // Extract relevant data
            $channel = $data->channel ?? null;
            $items = $channel->item ?? [];

            // Implement searching
            $search = $request->get('search');
            if (!empty($search)) {
                $items = array_filter($items, function ($item) use ($search) {
                    return stripos($item->title, $search) !== false;
                });
            }

            // Map data to include image URL and make it paginated
            $mappedItems = array_map(function ($item) {
                $description = $item->description ?? '';
                preg_match('/<img.+?src=[\'"](?P<src>.+?)[\'"].*?>/i', $description, $matches);
                $imageSrc = Arr::get($matches, 'src', '');
                
                return (object) [
                    'title' => $item->title ?? '',
                    'description' => strip_tags($item->description) ?? '',
                    'link' => $item->link ?? '',
                    'image' => $imageSrc,
                ];
            }, $items);

            // Paginate the articles
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 10;
            $pagedData = array_slice($mappedItems, ($currentPage - 1) * $perPage, $perPage);
            $articlesPaginated = new LengthAwarePaginator($pagedData, count($mappedItems), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]);

            return view('news.index', [
                'articles' => $articlesPaginated,
                'search' => $search,
            ]);

        } catch (\Exception $e) {
            // Handle any exceptions (e.g., Guzzle HTTP errors)
            return back()->withError('Failed to fetch news data.');
        }
    }
}
