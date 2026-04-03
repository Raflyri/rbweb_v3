<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Article;

class ArticleForm extends Component
{
    use WithFileUploads;

    public ?Article $article = null;
    public bool $isEdit = false;

    // Translatable arrays
    public array $title = ['id' => '', 'my' => '', 'en' => '', 'jp' => ''];
    public array $slug = ['id' => '', 'my' => '', 'en' => '', 'jp' => ''];
    public array $content = ['id' => '', 'my' => '', 'en' => '', 'jp' => ''];
    public array $excerpt = ['id' => '', 'my' => '', 'en' => '', 'jp' => ''];
    public array $meta_title = ['id' => '', 'my' => '', 'en' => '', 'jp' => ''];
    public array $meta_description = ['id' => '', 'my' => '', 'en' => '', 'jp' => ''];

    // Additional fields
    public $thumbnail;
    public $status = 'Draft';
    public $published_at;

    protected function rules()
    {
        return [
            'title.id' => 'required|string|max:255',
            'slug.id' => 'required|string|max:255',
            'title.*' => 'nullable|string|max:255',
            'slug.*' => 'nullable|string|max:255',
            'content.*' => 'nullable|string',
            'excerpt.*' => 'nullable|string',
            'meta_title.*' => 'nullable|string|max:255',
            'meta_description.*' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'required|in:Draft,Pending Review,Published,Scheduled',
            'published_at' => 'nullable|date',
        ];
    }

    public function mount(?Article $article = null)
    {
        if ($article && $article->exists) {
            $this->article = $article;
            $this->isEdit = true;
            $this->status = $article->status;
            $this->published_at = $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : null;

            foreach (['id', 'my', 'en', 'jp'] as $lang) {
                $this->title[$lang] = $article->getTranslation('title', $lang, false) ?? '';
                $this->slug[$lang] = $article->getTranslation('slug', $lang, false) ?? '';
                $this->content[$lang] = $article->getTranslation('content', $lang, false) ?? '';
                $this->excerpt[$lang] = $article->getTranslation('excerpt', $lang, false) ?? '';
                $this->meta_title[$lang] = $article->getTranslation('meta_title', $lang, false) ?? '';
                $this->meta_description[$lang] = $article->getTranslation('meta_description', $lang, false) ?? '';
            }
        }
    }

    public function updated($property, $value)
    {
        // Auto-generate slug when title changes
        if (Str::startsWith($property, 'title.')) {
            $lang = Str::after($property, 'title.');
            if (empty($this->slug[$lang]) && !empty($value)) {
                $this->slug[$lang] = Str::slug($value);
            }
        }
    }

    public function autoSave()
    {
        // Only run auto-save if primary title exists
        if (empty($this->title['id'])) {
            return;
        }

        // Auto-save always preserves it as a draft if it wasn't published yet
        if ($this->status !== 'Published' && $this->status !== 'Scheduled') {
             $this->status = 'Draft';
        }

        $this->saveState('Auto-saved');
    }

    public function saveDraft()
    {
        $this->status = 'Draft';
        $this->saveState('Artikel berhasil disimpan sebagai Draft!');
    }

    public function publishNow()
    {
        $this->status = 'Published';
        $this->published_at = now()->format('Y-m-d\TH:i');
        $this->saveState('Artikel berhasil di-publish!');
    }

    public function schedule()
    {
        $this->status = 'Scheduled';
        $this->saveState('Artikel berhasil dijadwalkan!');
    }

    public function save()
    {
        // Dynamic logic to handle save based on published_at
        if (!empty($this->published_at) && strtotime($this->published_at) > time()) {
            $this->status = 'Scheduled';
            $message = 'Artikel berhasil dijadwalkan!';
        } else {
            // Keep existing status or default to Pending Review if they hit save normally (unless already Draft)
            $message = 'Artikel berhasil disimpan!';
        }
        
        $this->saveState($message);
    }

    protected function saveState($successMessage)
    {
        $this->validate();

        // Ensure status is valid
        if (!in_array($this->status, ['Draft', 'Pending Review', 'Published', 'Scheduled'])) {
            $this->status = 'Draft';
        }

        // Clean out empty translations
        $cleanTranslations = function($arr) {
            return array_filter($arr, fn($val) => $val !== '' && $val !== null);
        };

        if (!$this->article) {
            $this->article = new Article();
            $this->article->user_id = auth()->id();
        }

        $this->article->status = $this->status;
        $this->article->published_at = $this->published_at ?: null;
        $this->article->setTranslations('title', $cleanTranslations($this->title));
        $this->article->setTranslations('slug', $cleanTranslations($this->slug));
        $this->article->setTranslations('content', $cleanTranslations($this->content));
        $this->article->setTranslations('excerpt', $cleanTranslations($this->excerpt));
        $this->article->setTranslations('meta_title', $cleanTranslations($this->meta_title));
        $this->article->setTranslations('meta_description', $cleanTranslations($this->meta_description));
        
        $this->article->save();
        $this->isEdit = true; // once created, it's in edit mode

        // Handle thumbnail if needed (skipping full implementation for brevity, but can be added)
        // ...

        session()->flash('message', $successMessage);
    }

    public function render()
    {
        return view('livewire.article-form');
    }
}
