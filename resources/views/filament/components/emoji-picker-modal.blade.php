{{--
    Emoji Picker Component for RichEditor Modal
    Provides categorized emoji browser, search, and one-click selection.
--}}
<div x-data="{
    search: '',
    activeCategory: 'popular',
    selected: '',
    categories: {
        popular: {
            name: '🔥 Populer',
            emojis: ['🚀', '⭐', '💡', '🔥', '✅', '❌', '⚠️', '📌', '🎯', '📝', '🔍', '📊', '📈', '📉', '💻', '🌐', '📱', '⚙️', '🛠️', '🔒', '🔑', '🏆', '💎', '⏳', '⏰', '📅', '🏷️', '📦', '🎁', '📣', '📢', '🔔', '💬', '✨', '🎉', '💯', '👍', '👏', '🤝', '🙌']
        },
        expression: {
            name: '😊 Ekspresi',
            emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '😉', '😌', '😍', '🥰', '😘', '😋', '😛', '😜', '🤪', '😎', '🤓', '🧐', '🤔', '🤫', '🤐', '😴', '🥳', '🤩', '🥺', '😏', '😬', '😮', '🤯', '😱', '🥱', '😷', '🤒', '🤕']
        },
        hands: {
            name: '👋 Isyarat',
            emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🫰', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿']
        },
        tech: {
            name: '💼 Bisnis & Tech',
            emojis: ['💻', '🖥️', '🖨️', '⌨️', '🖱️', '📱', '☎️', '📡', '🔋', '🔌', '💾', '💿', '📸', '📹', '🎙️', '📢', '💡', '🔦', '💵', '🪙', '💳', '💎', '⚖️', '📊', '📈', '📉', '📋', '📁', '📂', '📄', '📑', '📊', '💼', '🏷️', '📦', '🏢', '🏭', '🏛️', '🤖', '🧠']
        },
        symbols: {
            name: '⚡ Simbol & Tanda',
            emojis: ['✅', '❌', '✔️', '✖️', '➕', '➖', '➗', '⚠️', '⛔', '🚫', '🛑', '❗', '❓', '❕', '❔', '💯', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫', '⚪', '🟩', '🟦', '🟨', '🟥', '➡️', '⬅️', '⬆️', '⬇️', '↗️', '↘️', '↙️', '↖️', '↕️', '↔️', '🔄', '▶️', '⏸️', '⏹️', '🔘']
        },
        objects: {
            name: '🎨 Objek & Seni',
            emojis: ['🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🎸', '🎺', '🎻', '🥁', '🎮', '🕹️', '🎲', '🎯', '🎳', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🎫', '🎟️', '🎪', '🎭', '🧵', '🧶', '👓', '🕶️', '🥽', '👑', '👒', '🧢', '🎓', '🎒', '🧳', '☂️', '☕', '🍵', '🍕']
        }
    },
    get filteredEmojis() {
        if (!this.search.trim()) {
            return this.categories[this.activeCategory]?.emojis || [];
        }
        const term = this.search.toLowerCase();
        let all = [];
        Object.values(this.categories).forEach(c => all.push(...c.emojis));
        const unique = [...new Set(all)];
        return unique;
    },
    pick(char) {
        this.selected = char;
        const inputs = document.querySelectorAll('input[data-emoji-input], input[name*=\'emoji\'], [wire\\:model*=\'emoji\']');
        inputs.forEach(input => {
            input.value = char;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }
}" class="flex flex-col gap-3 py-1">
    {{-- Search and Categories Bar --}}
    <div class="flex flex-col gap-2.5">
        <div class="relative">
            <input
                type="text"
                x-model="search"
                placeholder="Cari emoji..."
                class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 pl-9 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <button
                x-show="search.length > 0"
                x-cloak
                @click="search = ''"
                type="button"
                class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            >
                ✕
            </button>
        </div>

        {{-- Category Pills --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar" x-show="!search.trim()">
            <template x-for="(cat, key) in categories" :key="key">
                <button
                    type="button"
                    @click="activeCategory = key"
                    :class="activeCategory === key
                        ? 'bg-red-600 text-white font-semibold shadow-sm'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap cursor-pointer"
                    x-text="cat.name"
                ></button>
            </template>
        </div>
    </div>

    {{-- Emoji Grid --}}
    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 bg-slate-50/50 dark:bg-slate-950/40 max-h-56 overflow-y-auto">
        <div class="grid grid-cols-8 sm:grid-cols-10 gap-1 text-center">
            <template x-for="(char, index) in filteredEmojis" :key="index">
                <button
                    type="button"
                    @click="pick(char)"
                    :class="selected === char ? 'bg-red-500/20 ring-2 ring-red-500 scale-110' : 'hover:bg-slate-200 dark:hover:bg-slate-800'"
                    class="h-9 w-9 flex items-center justify-center text-xl rounded-lg transition-transform duration-100 hover:scale-125 cursor-pointer select-none"
                    :title="char"
                    x-text="char"
                ></button>
            </template>
        </div>
    </div>

    <div class="text-[11px] text-slate-500 dark:text-slate-400 text-center">
        💡 Klik salah satu emoji di atas untuk memilih, lalu klik <strong>Sisipkan Emoji</strong>.
    </div>
</div>
