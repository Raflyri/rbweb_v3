<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RBeverything | Portfolio & News</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/scroll-effects.js'])
</head>

<body class="antialiased bg-slate-950 text-white overflow-x-hidden font-['Inter'] selection:bg-cyan-500/30">
    <!-- Add a cinematic video background -->
    <div class="fixed top-0 left-0 w-full h-full -z-10 overflow-hidden">
        <!-- Simulated video background with gradient fallback -->
        <div class="w-full h-full bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/40 via-slate-950 to-black animate-pulse opacity-80 duration-[10000ms]"></div>

        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMykiLz48L3N2Zz4=')] opacity-50 mix-blend-overlay"></div>
    </div>

    <nav class="fixed w-full z-50 glass-nav transition-all duration-300 py-4">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-12">
                <div class="flex items-center">
                    <span class="font-black text-2xl tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-500 to-indigo-500 hover:scale-105 transition-transform cursor-pointer">RBeverything</span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="/client-area" class="text-sm font-semibold text-slate-300 hover:text-cyan-400 transition-colors">Client Area</a>
                    <a href="/rbdashboard" class="text-sm font-semibold text-slate-300 hover:text-blue-400 transition-colors">Admin</a>

                    <div class="px-2 py-1 rounded-md bg-white/5 border border-white/10 flex items-center">
                        <svg class="w-4 h-4 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <select class="bg-transparent text-xs font-semibold text-slate-300 outline-none cursor-pointer appearance-none">
                            <option value="en" class="bg-slate-900">EN-US</option>
                            <option value="id" class="bg-slate-900">ID</option>
                            <option value="ja" class="bg-slate-900">JA</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Zoom on Scroll -->
    <main>
        <section class="h-[120vh] flex flex-col justify-center items-center text-center px-4 relative zoom-section -mt-20">
            <div class="relative z-10 w-full max-w-5xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 backdrop-blur-md mb-8 shadow-2xl">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-medium text-slate-300 tracking-wide uppercase">System Ecosystem V3 Active</span>
                </div>

                <h1 class="text-6xl md:text-8xl lg:text-9xl font-black mb-6 tracking-tighter drop-shadow-2xl hero-title leading-tight">
                    Digital <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-500 to-indigo-500 animate-gradient-x">Excellence</span>
                </h1>
                <p class="text-xl md:text-3xl max-w-3xl mx-auto text-slate-400 font-light mb-12 hero-subtitle leading-relaxed">
                    A cinematic platform featuring dynamic portfolios, tech news, and glassmorphism design.
                </p>

                <div class="hero-actions flex gap-4 justify-center opacity-100 transition-all duration-300">
                    <a href="/client-area/register" class="bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-bold py-4 px-8 rounded-full shadow-lg shadow-blue-500/30 transition-all hover:scale-105 hover:shadow-blue-500/50">
                        Build Portfolio
                    </a>
                    <a href="#ecosystem" class="bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/10 text-white font-semibold py-4 px-8 rounded-full transition-all hover:scale-105">
                        Explore Ecosystem
                    </a>
                </div>
            </div>

            <div class="scroll-indicator absolute bottom-32 animate-bounce">
                <svg class="w-8 h-8 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </section>

        <!-- Bento Grid Section -->
        <section id="ecosystem" class="min-h-screen py-32 px-4 md:px-8 max-w-7xl mx-auto relative z-10">
            <div class="text-center mb-20 section-header">
                <h2 class="text-5xl md:text-6xl font-black mb-6 tracking-tight">The Ecosystem</h2>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto">Discover the interconnected modules powering the RBeverything platform.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-[auto] gap-6 bento-grid">
                <!-- Glassmorphism Card 1: Portfolios -->
                <div class="bento-item glass-card col-span-1 md:col-span-2 row-span-2 p-10 rounded-[2.5rem] group flex flex-col justify-between overflow-hidden">
                    <div class="relative z-10">
                        <span class="px-4 py-1.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-full text-xs font-bold uppercase tracking-widest mb-6 inline-block">Portfolios</span>
                        <h3 class="text-4xl font-bold mb-4 tracking-tight group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r from-blue-400 to-cyan-300 transition-all duration-300">Custom User Pages</h3>
                        <p class="text-lg text-slate-400 font-light pr-8">Every user gets a personalized `/@slug` portfolio to showcase experiences, skills, and achievements using our sleek design system.</p>
                    </div>
                    <div class="mt-12 relative h-48 bg-slate-900/50 rounded-2xl overflow-hidden shadow-2xl border border-white/5 transform group-hover:scale-105 transition-transform duration-700">
                        <!-- Visual placeholder code editor effect -->
                        <div class="flex items-center px-4 py-3 bg-slate-900 border-b border-white/5">
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                            </div>
                        </div>
                        <div class="p-4 font-mono text-sm text-blue-300/80 whitespace-pre">const <span class="text-cyan-400">user</span> = {
                            slug: <span class="text-emerald-300">'@rafly'</span>,
                            role: <span class="text-emerald-300">'Premium'</span>,
                            skills: [<span class="text-emerald-300">'Laravel'</span>, <span class="text-emerald-300">'Filament'</span>]
                            };</div>
                    </div>
                </div>

                <!-- Glassmorphism Card 2: News & Blogs -->
                <div class="bento-item glass-card col-span-1 md:col-span-2 p-8 rounded-[2rem] group flex flex-col justify-between">
                    <div>
                        <span class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-xs font-bold uppercase tracking-widest mb-6 inline-block">News & Blogs</span>
                        <h3 class="text-2xl font-bold mb-3 tracking-wide">Auto-Pilot Publishing</h3>
                        <p class="text-slate-400 font-light leading-relaxed">Time-travel scheduling via Spatie Translatable across ID, EN, JA, MS, and EN-GB.</p>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Glassmorphism Card 3: Architecture -->
                <div class="bento-item glass-card col-span-1 p-8 rounded-[2rem] group flex flex-col justify-between bg-gradient-to-br from-indigo-900/40 to-slate-900/60">
                    <div>
                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 rounded-full text-[10px] font-bold uppercase tracking-widest mb-4 inline-block">Security</span>
                        <h3 class="text-xl font-bold mb-2">RBAC Control</h3>
                        <p class="text-sm text-indigo-200/70">Dual dashboards enforcing strict policy.</p>
                    </div>
                    <svg class="w-10 h-10 mt-6 text-indigo-400 opacity-50 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>

                <!-- Glassmorphism Card 4: Global -->
                <div class="bento-item glass-card col-span-1 p-8 rounded-[2rem] group flex flex-col justify-between">
                    <div>
                        <span class="px-3 py-1 bg-orange-500/10 text-orange-400 border border-orange-500/20 rounded-full text-[10px] font-bold uppercase tracking-widest mb-4 inline-block">i18n</span>
                        <h3 class="text-xl font-bold mb-2">Global Ready</h3>
                        <p class="text-sm text-slate-400">Native multi-language integrations.</p>
                    </div>
                    <div class="flex mt-6 gap-2">
                        <span class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">EN</span>
                        <span class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">JA</span>
                        <span class="w-8 h-8 rounded bg-orange-500/20 text-orange-400 border border-orange-500/30 flex items-center justify-center text-xs font-bold">ID</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>