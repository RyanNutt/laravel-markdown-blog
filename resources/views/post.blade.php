{{-- View for a blog post

$post variable is the post --}}

<!DOCTYPE html>
<html>

<head>
    <title>{{ $post->title }}</title>
</head>

<body>
    <div style="margin: 0 auto; width: 65ch;">
        <h1>{{ $post->title }}</h1>
        <p>{!! $post->html() !!}</p>
    </div>

</body>

</html>
