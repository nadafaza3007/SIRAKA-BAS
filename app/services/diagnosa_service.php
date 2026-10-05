<?php

namespace App\Services;

use App\Models\AturanDiagnosa;

class DiagnosaService
{
    private function preprocess(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', '', $text);
        return array_filter(explode(' ', $text));
    }

    private function calculateSimilarity(string $text1, string $text2): float
    {
        $words1 = array_unique($this->preprocess($text1));
        $words2 = array_unique($this->preprocess($text2));

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        if (count($union) === 0) {
            return 0.0;
        }

        return count($intersection) / count($union);
    }

    public function prediksi(string $keluhanInput): array
    {
        $datasets = AturanDiagnosa::all();
        $bestMatch = null;
        $highestScore = 0;

        foreach ($datasets as $data) {
            $score = $this->calculateSimilarity($keluhanInput, $data->keluhan_text);
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $data;
            }
        }

        if ($highestScore >= 0.20 && $bestMatch) {
            return [
                'diagnosa' => $bestMatch->diagnosa_awal,
                'skor' => round($highestScore * 100, 2) . '%'
            ];
        }

        return [
            'diagnosa' => 'Pemeriksaan Lebih Lanjut oleh Mekanik/Teknisi',
            'skor' => '0%'
        ];
    }

    // Fungsi otomatis menambah data baru saat servis selesai
    public function simpanDatasetBaru(string $keluhanBaru, string $diagnosaFinal): AturanDiagnosa
    {
        return AturanDiagnosa::create([
            'keluhan_text' => $keluhanBaru,
            'diagnosa_awal' => $diagnosaFinal,
        ]);
    }
}