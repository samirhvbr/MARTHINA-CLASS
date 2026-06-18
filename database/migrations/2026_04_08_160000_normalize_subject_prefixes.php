<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $subjectPrefixes = [
        'english' => 'eng_',
        'eng_' => 'eng_',
        'portuguese' => 'prt_',
        'prt_' => 'prt_',
        'mathematics' => 'mat_',
        'mat_' => 'mat_',
    ];

    private array $legacyPrefixes = [
        'english-',
        'eng_',
        'portuguese-',
        'prt_',
        'mathematics-',
        'mat_',
    ];

    public function up(): void
    {
        $subjects = DB::table('subjects')->select('id', 'slug')->get();

        foreach ($subjects as $subject) {
            $newSlug = $this->subjectPrefixes[$subject->slug] ?? $subject->slug;

            if ($newSlug !== $subject->slug) {
                DB::table('subjects')->where('id', $subject->id)->update(['slug' => $newSlug]);
            }
        }

        $subjectsById = DB::table('subjects')->pluck('slug', 'id');
        $categories = DB::table('categories')->select('id', 'subject_id', 'slug', 'name')->get();

        foreach ($categories as $category) {
            $subjectSlug = (string) ($subjectsById[$category->subject_id] ?? '');

            if (!in_array($subjectSlug, ['eng_', 'prt_', 'mat_'], true)) {
                continue;
            }

            $baseSlug = $this->stripKnownPrefix((string) $category->slug);

            if ($baseSlug === '') {
                $baseSlug = Str::slug((string) $category->name, '-');
            }

            $newSlug = $subjectSlug . $baseSlug;

            if ($newSlug !== $category->slug) {
                DB::table('categories')->where('id', $category->id)->update(['slug' => $newSlug]);
            }
        }
    }

    public function down(): void
    {
        $subjectRollbackMap = [
            'eng_' => 'english',
            'prt_' => 'portuguese',
            'mat_' => 'mathematics',
        ];

        $categoryRollbackPrefixes = [
            'eng_' => 'english-',
            'prt_' => 'portuguese-',
            'mat_' => 'mathematics-',
        ];

        $subjects = DB::table('subjects')->select('id', 'slug')->get();

        foreach ($subjects as $subject) {
            $oldSlug = $subjectRollbackMap[$subject->slug] ?? $subject->slug;

            if ($oldSlug !== $subject->slug) {
                DB::table('subjects')->where('id', $subject->id)->update(['slug' => $oldSlug]);
            }
        }

        $subjectsById = DB::table('subjects')->pluck('slug', 'id');
        $categories = DB::table('categories')->select('id', 'subject_id', 'slug', 'name')->get();

        foreach ($categories as $category) {
            $subjectSlug = (string) ($subjectsById[$category->subject_id] ?? '');
            $prefix = $categoryRollbackPrefixes[$this->subjectPrefixes[$subjectSlug] ?? $subjectSlug] ?? null;

            if (!$prefix) {
                continue;
            }

            $baseSlug = $this->stripKnownPrefix((string) $category->slug);

            if ($baseSlug === '') {
                $baseSlug = Str::slug((string) $category->name, '-');
            }

            $oldSlug = $prefix . $baseSlug;

            if ($oldSlug !== $category->slug) {
                DB::table('categories')->where('id', $category->id)->update(['slug' => $oldSlug]);
            }
        }
    }

    private function stripKnownPrefix(string $slug): string
    {
        foreach ($this->legacyPrefixes as $prefix) {
            if (Str::startsWith($slug, $prefix)) {
                return Str::after($slug, $prefix);
            }
        }

        return $slug;
    }
};
