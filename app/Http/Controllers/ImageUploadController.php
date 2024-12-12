<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use GuzzleHttp\Client;

class ImageUploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $roboflowDir = public_path('uploads/roboflow');
    
        // Periksa apakah direktori ada, jika tidak buat
        if (!File::exists($roboflowDir)) {
            File::makeDirectory($roboflowDir, 0755, true);
        }
    
        // Menghapus semua file dalam direktori sebelum menyimpan gambar baru
        $files = File::files($roboflowDir);
        foreach ($files as $file) {
            File::delete($file);
        }
    
        // Validasi apakah ada file yang diunggah
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imagePath = $roboflowDir . '/' . $imageFile->getClientOriginalName();
            
            // Simpan file ke direktori
            $imageFile->move($roboflowDir, $imageFile->getClientOriginalName());
    
            // Inisialisasi Guzzle untuk mengirim ke Node.js
            $client = new Client();
            try {
                // Membaca gambar sebagai base64
                $imageData = base64_encode(file_get_contents($imagePath));
    
                $response = $client->post('http://localhost:3000/upload', [
                    'multipart' => [
                        [
                            'name'     => 'image', // Harus sama dengan fieldname di Node.js
                            'contents' => fopen($imagePath, 'r'), // File yang diupload
                            'filename' => $imageFile->getClientOriginalName()
                        ]
                    ]
                ]);
                
    
                $result = json_decode($response->getBody()->getContents(), true);
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Gambar berhasil diunggah!',
                    'data' => $result
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghubungi Roboflow',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada file yang diunggah.'
        ], 400);
    }
}
