<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\TranslationLoader\LanguageLine;

class LanguageLineSeeder extends Seeder
{
    public function run(): void
    {
        $groups = ['messages'];
        $locales = ['en', 'id'];

        foreach ($groups as $group) {
            $keys = [];
            foreach ($locales as $locale) {
                $path = lang_path("{$locale}/{$group}.php");
                if (! file_exists($path)) {
                    continue;
                }
                foreach (require $path as $key => $text) {
                    $keys[$key][$locale] = $text;
                }
            }
            foreach ($keys as $key => $text) {
                LanguageLine::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['text' => $text],
                );
            }
        }
    }
}
