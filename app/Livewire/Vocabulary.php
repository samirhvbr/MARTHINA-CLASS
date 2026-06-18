<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\Word;

class Vocabulary extends Component
{
    public $categories;
    public $selectedCategory = null;
    public $words = [];
    public $showPortuguese = false;

    public function mount()
    {
        $this->categories = Category::all();
        // $words já inicia vazio = []
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->words = Word::where('category_id', $categoryId)
            ->inRandomOrder()
            ->get()
            ->toArray();  // transforma em array simples para empty() funcionar
        $this->showPortuguese = false;
    }

    public function togglePortuguese()
    {
        $this->showPortuguese = !$this->showPortuguese;
    }

    public function backToCategories()
    {
        $this->selectedCategory = null;
        $this->words = [];
    }

    public function speak($text)
    {
        $this->dispatch('speak', text: $text);
    }

    public function render()
    {
        return view('livewire.vocabulary');
    }
}