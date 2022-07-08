@php echo '<?xml version="1.0" encoding="UTF-8"@endphp'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="https://www.google.com/schemas/sitemap-image/1.1">
    @foreach ($posts as $post)
        <url>
            <loc>{{ url($post->permalink) }}</loc>
            <lastmod>{{ $post->publish_date->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>{{ $post->sitemap_frequency }}</changefreq>
            <priority>{{ $post->sitemap_priority }}</priority>
            @if ($post->image)
                <image:image>
                    <image:loc>{{ url($post->image) }}</image:loc>
                </image:image>
            @endif
        </url>
    @endforeach
</urlset>
