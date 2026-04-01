<x-filament-panels::page>
    <div class="articulate-editor-wrapper">
        <link rel="stylesheet" href="{{ asset('css/articulate/create.css') }}">
        
        <form wire:submit.prevent="create" class="articulate-editor-container">
            <!-- Center Editor -->
            <div class="articulate-center-editor">
                <!-- Cover Image Zone -->
                <div class="cover-image-placeholder" onclick="document.getElementById('thumbnail-input').click()">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span id="cover-text">Klik untuk tambah gambar sampul</span>
                </div>

                <!-- Tags Row -->
                <div class="mb-4">
                    <label class="articulate-section-label">TAG KATEGORI</label>
                    <select multiple wire:model="data.tags" class="articulate-select">
                        @foreach($this->tags as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Title Input -->
                <input type="text" 
                       wire:model.defer="data.title.id" 
                       placeholder="Judul artikel Anda..." 
                       class="articulate-title-input">

                <div class="w-full h-px bg-gray-200 mb-6"></div>

                <!-- Body Textarea / Content -->
                <textarea id="articulate-body"
                          wire:model.defer="data.content.id"
                          placeholder="Tulis konten artikel Anda di sini..."
                          class="articulate-body-textarea"></textarea>

                <!-- Footer Stats -->
                <div class="articulate-editor-footer">
                    <span id="word-count-display">0 kata · ~1 mnt baca</span>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="articulate-right-panel">
                <!-- Properties Section -->
                <div class="articulate-section">
                    <label class="articulate-section-label">PROPERTI</label>
                    
                    <div class="articulate-card mb-4">
                        <div class="articulate-avatar">AR</div>
                        <div>
                            <div class="text-xs font-bold">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-gray-400">Editor</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-[10px] uppercase font-bold text-gray-400 mb-1 block">Status</label>
                        <select wire:model="data.status" class="articulate-select">
                            <option value="Draft">Draft</option>
                            <option value="Pending Review">Pending Review</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="text-[10px] uppercase font-bold text-gray-400 mb-1 block">Jadwal Publish</label>
                        <input type="datetime-local" wire:model="data.published_at" class="articulate-select">
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="articulate-section">
                    <label class="articulate-section-label">SEO</label>
                    
                    <div class="mb-4">
                        <label class="text-[10px] uppercase font-bold text-gray-400 mb-1 block">Meta Description</label>
                        <textarea id="meta-description"
                                  wire:model.defer="data.meta_description.id" 
                                  rows="4" 
                                  class="articulate-select resize-none" 
                                  placeholder="Summary for search engines..."></textarea>
                        <div class="text-right text-[10px] text-gray-400 mt-1" id="meta-count-display">0 / 160</div>
                    </div>
                </div>

                <!-- Actions -->
                <button type="submit" class="articulate-publish-btn">
                    Publish Artikel
                </button>
            </div>
        </form>

        <script src="{{ asset('js/articulate/create.js') }}"></script>
    </div>
</x-filament-panels::page>
