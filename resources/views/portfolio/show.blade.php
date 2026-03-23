<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->user->name }} | Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-slate-950 text-white font-['Inter'] selection:bg-cyan-500/30">
    <!-- Background -->
    <div class="fixed top-0 left-0 w-full h-full -z-10 overflow-hidden">
        <div class="w-full h-full bg-[radial-gradient(ellipse_at_top,var(--tw-gradient-stops))] from-indigo-900/20 via-slate-950 to-black"></div>
    </div>

    <nav class="w-full glass-nav py-4 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-6 flex justify-between items-center">
            <a href="/" class="font-bold text-xl text-slate-300 hover:text-white transition">← RBeverything</a>
            <div class="font-semibold text-sm bg-white/10 px-3 py-1.5 rounded-full border border-white/10">
                {{ $profile->custom_url_slug }}
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-16">
        <!-- Header section -->
        <header class="text-center mb-16 animate-fade-in-up">
            @if($profile->avatar_url)
            <img src="{{ $profile->avatar_url }}" alt="{{ $profile->user->name }}" class="w-32 h-32 rounded-full mx-auto mb-6 border-4 border-slate-800 shadow-xl object-cover">
            @else
            <div class="w-32 h-32 rounded-full mx-auto mb-6 bg-linear-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-4xl font-black shadow-xl">
                {{ substr($profile->user->name, 0, 1) }}
            </div>
            @endif

            <h1 class="text-5xl font-black mb-4">{{ $profile->user->name }}</h1>
            <p class="text-xl text-cyan-400 font-semibold mb-6">{{ $profile->headline ?? 'Digital Creator' }}</p>
            <p class="text-slate-400 max-w-2xl mx-auto leading-relaxed">{{ $profile->bio }}</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Main Column (Experiences, Educations) -->
            <div class="md:col-span-2 space-y-8">
                @if($profile->user->experiences->isNotEmpty())
                <section class="glass-card p-8 rounded-3xl">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                        <span class="p-2 bg-blue-500/20 text-blue-400 rounded-lg"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg></span>
                        Experience
                    </h2>
                    <div class="space-y-6">
                        @foreach($profile->user->experiences as $exp)
                        <div class="border-l-2 border-slate-700 pl-4 relative">
                            <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px] top-1.5 ring-4 ring-slate-900"></div>
                            <h3 class="text-lg font-bold text-white">{{ $exp->role }}</h3>
                            <p class="text-cyan-400 font-medium text-sm mb-2">{{ $exp->company }} <span class="text-slate-500 mx-2">•</span> {{ $exp->start_date->format('M Y') }} - {{ $exp->end_date ? $exp->end_date->format('M Y') : 'Present' }}</p>
                            <p class="text-slate-400 text-sm leading-relaxed">{{ $exp->description }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                @if($profile->user->posts->isNotEmpty())
                <section class="glass-card p-8 rounded-3xl">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                        <span class="p-2 bg-emerald-500/20 text-emerald-400 rounded-lg"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg></span>
                        Publications
                    </h2>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($profile->user->posts as $post)
                        <article class="p-4 bg-slate-800/50 hover:bg-slate-800 rounded-2xl border border-white/5 transition cursor-pointer">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-2 block">{{ $post->type }}</span>
                            <h3 class="text-lg font-bold mb-2">{{ $post->title }}</h3>
                            <div class="text-slate-400 text-sm line-clamp-2 prose prose-invert">{!! $post->content !!}</div>
                        </article>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            <!-- Sidebar (Skills, Educations) -->
            <div class="space-y-8">
                @if($profile->user->skills->isNotEmpty())
                <section class="glass-card p-6 rounded-3xl">
                    <h2 class="text-xl font-bold mb-4">Skills</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($profile->user->skills as $skill)
                        <span class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-lg text-sm text-slate-300">{{ $skill->skill_name }}</span>
                        @endforeach
                    </div>
                </section>
                @endif

                @if($profile->user->education->isNotEmpty())
                <section class="glass-card p-6 rounded-3xl">
                    <h2 class="text-xl font-bold mb-4">Education</h2>
                    <div class="space-y-4">
                        @foreach($profile->user->education as $edu)
                        <div>
                            <h3 class="text-md font-bold text-white">{{ $edu->degree }}</h3>
                            <p class="text-cyan-400 text-sm mb-1">{{ $edu->institution }}</p>
                            <p class="text-slate-500 text-xs">{{ $edu->start_date->format('Y') }} - {{ $edu->end_date ? $edu->end_date->format('Y') : 'Present' }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>
        </div>
    </main>
</body>

</html>