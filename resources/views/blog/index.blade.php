@extends('layouts.brand')

@section('title', 'The Doughmain Bakery Blog: Tips, Guides & Business Growth')
@section('meta_description', 'Read guides, marketing strategies, SEO tips, and software reviews to help grow your micro-bakery or custom cake business.')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">📚 Doughmain Knowledge Hub</span>
        <h1>Growing Your Bakery Business Online</h1>
        <p>Expert advice, marketing blueprints, SEO guides, and business tips designed specifically for artisanal bakers and custom cake designers.</p>
    </div>
</section>

<section class="section-padding" style="background-color: var(--white);">
    <div class="container">
        <div class="blog-grid">
            @foreach($posts as $slug => $post)
                <article class="blog-card">
                    <div class="blog-card-img">
                        {{ $post['emoji'] }}
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-meta">
                            <span>{{ $post['category'] }}</span> &middot; <span>{{ $post['read_time'] }}</span>
                        </div>
                        <h3><a href="/blog/{{ $slug }}">{{ $post['title'] }}</a></h3>
                        <p>{{ $post['description'] }}</p>
                        <a href="/blog/{{ $slug }}" class="blog-card-link">Read Article →</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
