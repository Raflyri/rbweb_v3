<div class="mb-4">
    <style>
        /* Perbaikan Toolbar Editor Overlap & Mencegahnya meledak keluar grid */
        .fi-fo-rich-editor-toolbar {
            flex-wrap: wrap !important;
        }
    </style>
    <div class="flex space-x-1 rounded-xl bg-gray-100/80 p-1 dark:bg-gray-800">
        @php
            $locales = [
                'id' => 'Indonesia',
                'my' => 'Melayu',
                'en' => 'English',
                'jp' => '日本語'
            ];
        @endphp
        @foreach($locales as $code => $label)
            <button
                type="button"
                x-on:click="activeTab = '{{ $code }}'"
                :class="{
                    'bg-white text-primary-600 shadow-sm dark:bg-white/10 dark:text-primary-400 ring-1 ring-gray-900/5': activeTab === '{{ $code }}',
                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200': activeTab !== '{{ $code }}'
                }"
                class="flex-1 px-3 py-2.5 text-sm font-medium rounded-lg transition-all focus:outline-none flex justify-center items-center gap-1.5"
            >
                <span class="uppercase tracking-wider font-bold">{{ $code }}</span>
                <span class="hidden sm:inline text-xs font-medium opacity-80">- {{ $label }}</span>
            </button>
        @endforeach
    </div>
</div>
