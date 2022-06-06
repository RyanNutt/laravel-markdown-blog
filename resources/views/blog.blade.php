{{-- View for a blog index page 

$posts variable is a paginator object. --}}
<!DOCTYPE html>
<html>

<head>
    <title>Blog Index</title>
</head>

<body>
    <div style="margin: 0 auto; width: 65ch;">
        <h1>Blog Index</h1>
        @foreach ($posts->items() as $post)
            <div style="margin: 1em 0;">
                <a href="{{ $post->url }}">
                    <h2>{{ $post->title }}</h2>
                </a>
                <p>{{ $post->description }}</p>
            </div>
        @endforeach

        @if ($posts->currentPage() > 1)
            <a href="{{ $posts->previousPageUrl() }}">Previous Page</a>
        @endif
        @if ($posts->hasMorePages())
            <a href="{{ $posts->nextPageUrl() }}">Next Page</a>
        @endif

    </div>

</body>

</html>
