<?php

namespace Database\Seeders;

use App\Models\SubmissionCategory;
use Illuminate\Database\Seeder;

class SubmissionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'UAS' => 'Pengumpulan Ujian Akhir Semester.',
            'UTS' => 'Pengumpulan Ujian Tengah Semester.',
            'Tugas' => 'Pengumpulan tugas praktikum.',
            'Tambahan' => 'Pengumpulan tambahan sesuai arahan pengajar.',
        ];

        foreach ($categories as $name => $description) {
            SubmissionCategory::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        }
    }
}
