@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Times of India RSS Feed</h1>

        {{-- Search Form --}}
        <form action="{{ route('news.index') }}" method="GET">
            <div class="form-group">
                <input type="text" class="form-control" name="search" placeholder="Search by title" value="{{ $search }}">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        {{-- Table to Display News --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Link</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($articles as $article)
                    <tr>
                        <td>{{ $article->title ?: 'No title' }}</td>
                        <td>{!! $article->description ?: 'No Description' !!}</td>
                        <td>
                            @if ($article->image)
                                <img src="{{ $article->image }}" alt="Image" style="max-width: 150px;">
                            @else
                                No Image Available
                            @endif
                        </td>
                        <td><a href="{{ $article->link }}" target="_blank">Read More</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination Links --}}
        <div class="mt-4">
            {{ $articles->appends(['search' => $search])->links() }}
        </div>
    </div>
@endsection
