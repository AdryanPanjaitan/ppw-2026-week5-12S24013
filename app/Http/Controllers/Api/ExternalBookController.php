<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExternalBookController extends Controller
{
    public function show(Buku $buku): JsonResponse
    {
        try {
            $response = Http::timeout(8)->acceptJson()->get('https://openlibrary.org/api/books', [
                'bibkeys' => 'ISBN:'.$buku->isbn,
                'format' => 'json',
                'jscmd' => 'data',
            ]);
        } catch (Throwable) {
            return response()->json([
                'type' => 'about:blank', 'title' => 'Bad Gateway', 'status' => 502,
                'detail' => 'Layanan OpenLibrary mengalami timeout atau tidak tersedia.',
            ], 502, ['Content-Type' => 'application/problem+json']);
        }

        if ($response->failed()) {
            return response()->json([
                'type' => 'about:blank', 'title' => 'Bad Gateway', 'status' => 502,
                'detail' => 'Layanan OpenLibrary tidak dapat diakses.',
            ], 502, ['Content-Type' => 'application/problem+json']);
        }

        $data = $response->json()['ISBN:'.$buku->isbn] ?? null;
        if (! $data) {
            return response()->json([
                'type' => 'about:blank', 'title' => 'Not Found', 'status' => 404,
                'detail' => 'Referensi ISBN tidak ditemukan di OpenLibrary.',
            ], 404, ['Content-Type' => 'application/problem+json']);
        }

        return response()->json(['data' => [
            'isbn' => $buku->isbn,
            'judul' => $data['title'] ?? null,
            'penulis' => collect($data['authors'] ?? [])->pluck('name')->values(),
            'penerbit' => collect($data['publishers'] ?? [])->pluck('name')->values(),
            'sumber' => 'OpenLibrary',
        ]]);
    }
}
