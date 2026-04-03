@assets
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <style>
        trix-toolbar [data-trix-button-group="file-tools"] {
            display: none;
        }
        .trix-content {
             min-height: 400px !important;
        }
    </style>
@endassets

@php
    $languages = [
        'id' => 'Indonesia',
        'my' => 'Melayu',
        'en' => 'English',
        'jp' => '日本語'
    ];
@endphp

<div>
    <!-- Autosave trigger every 10 seconds -->
    <form wire:submit.prevent="save" wire:poll.10s="autoSave">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ activeTab: 'id' }">
            
            <!-- Area Utama (8 Kolom) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Tab Header -->
                    <div class="flex border-b border-gray-200 bg-gray-50/50 overflow-x-auto">
                        @foreach($languages as $code => $label)
                            <button type="button" 
                                @click="activeTab = '{{ $code }}'"
                                :class="activeTab === '{{ $code }}' 
                                    ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold bg-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                                class="px-6 py-3 text-sm transition-colors duration-150 focus:outline-none whitespace-nowrap">
                                {{ strtoupper($code) }} - {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        @foreach($languages as $code => $label)
                            <div x-show="activeTab === '{{ $code }}'" style="display: none;" x-cloak class="flex flex-col gap-6">
                                
                                <!-- Judul -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel ({{ strtoupper($code) }})</label>
                                    <input type="text" 
                                        wire:model.blur="title.{{ $code }}" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Ketik judul di sini...">
                                    @error("title.$code") <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Slug -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug ({{ strtoupper($code) }})</label>
                                    <input type="text" 
                                        wire:model="slug.{{ $code }}" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="auto-generated-slug">
                                    @error("slug.$code") <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Excerpt -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan / Excerpt ({{ strtoupper($code) }})</label>
                                    <textarea 
                                        wire:model="excerpt.{{ $code }}" 
                                        rows="2"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Ringkasan singkat..."></textarea>
                                </div>

                                <!-- Trix Editor -->
                                <div wire:ignore>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Konten ({{ strtoupper($code) }})</label>
                                    <div x-data="{ 
                                            content: @entangle('content.' . $code),
                                            init() {
                                                // Initialize Trix with existing content if any
                                                let editor = this.$refs.trix.editor;
                                                if (this.content) {
                                                    editor.loadHTML(this.content);
                                                }
                                                // Listen to changes from Trix
                                                this.$refs.trix.addEventListener('trix-change', (e) => {
                                                    this.content = e.target.value;
                                                });
                                                // If Livewire updates content from outside, sync back to Trix
                                                this.$watch('content', (val) => {
                                                    if (val !== this.$refs.trix.value) {
                                                        // Avoid losing cursor position by only loading HTML if it's external change
                                                        let currentSelection = editor.getSelectedRange();
                                                        editor.loadHTML(val || '');
                                                        editor.setSelectedRange(currentSelection);
                                                    }
                                                });
                                            }
                                        }">
                                        <input id="trix-input-{{ $code }}" type="hidden" :value="content">
                                        <trix-editor x-ref="trix" input="trix-input-{{ $code }}" class="trix-content min-h-[400px] bg-white rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></trix-editor>
                                    </div>
                                    @error("content.$code") <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Sidebar (4 Kolom) -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                
                <!-- Status Publikasi -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Publikasi</h3>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Draft">Draft</option>
                            <option value="Pending Review">Pending Review</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Published">Published</option>
                        </select>
                        @error('status') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal Publish (Opsional)</label>
                        <input type="datetime-local" wire:model.live="published_at" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika publish sekarang. Tombol Simpan akan berubah otomatis menjadi <b>Schedule</b> jika tanggalnya di masa depan.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <!-- Dynamic Main Save/Schedule Button -->
                        @php
                            $isScheduled = !empty($published_at) && strtotime($published_at) > time();
                        @endphp
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white transition-colors {{ $isScheduled ? 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span wire:loading.remove wire:target="save">
                                @if($isScheduled)
                                    Jadwalkan (Schedule)
                                @else
                                    Simpan Perubahan
                                @endif
                            </span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" wire:click="saveDraft" class="flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                                <span wire:loading wire:target="saveDraft">Menyimpan...</span>
                            </button>

                            <button type="button" wire:click="publishNow" onclick="confirm('Yakin ingin publish artikel ini sekarang juga?') || event.stopImmediatePropagation()" class="flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <span wire:loading.remove wire:target="publishNow">Publish Now</span>
                                <span wire:loading wire:target="publishNow">Memproses...</span>
                            </button>
                        </div>
                    </div>
                    
                    @if (session()->has('message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mt-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('message') }}
                        </div>
                    @endif
                </div>

                <!-- Cover Image -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Cover Image</h3>
                    
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                <p class="mb-1 text-sm text-gray-500"><span class="font-semibold">Click to upload</span></p>
                                <p class="text-xs text-gray-500">Max size 2MB</p>
                            </div>
                            <input type="file" wire:model="thumbnail" class="hidden" accept="image/*" />
                        </label>
                    </div>
                    @if ($thumbnail)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                            <img src="{{ $thumbnail->temporaryUrl() }}" class="rounded-lg w-full h-auto object-cover border border-gray-200 shadow-sm">
                        </div>
                    @endif
                    @error('thumbnail') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- SEO Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="{ openSEO: false }">
                    <button type="button" @click="openSEO = !openSEO" class="flex items-center justify-between w-full p-6 text-left focus:outline-none rounded-t-xl hover:bg-gray-50 transition-colors">
                        <h3 class="font-semibold text-gray-800">SEO Settings</h3>
                        <svg :class="{'rotate-180': openSEO}" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="openSEO" x-collapse x-cloak class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                        @foreach($languages as $code => $label)
                            <div x-show="activeTab === '{{ $code }}'" style="display: none;" class="space-y-4">
                                <p class="text-xs font-semibold text-indigo-600 mb-3 border-b pb-2 uppercase tracking-wide">SEO: {{ $label }}</p>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                                    <input type="text" 
                                        wire:model.blur="meta_title.{{ $code }}" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                    <textarea 
                                        wire:model.blur="meta_description.{{ $code }}" 
                                        rows="4"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>
