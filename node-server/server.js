const express = require('express');
const multer = require('multer');
const axios = require('axios');
const path = require('path');
const fs = require('fs');
const FormData = require('form-data');

// Inisialisasi aplikasi Express
const app = express();
const port = 3000;

// Direktori penyimpanan file sementara
const uploadsDir = 'uploads/';

// Cek dan buat folder uploads jika tidak ada
if (!fs.existsSync(uploadsDir)) {
  fs.mkdirSync(uploadsDir, { recursive: true });
}

// Konfigurasi multer untuk menangani upload file
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, uploadsDir); // Menyimpan file di direktori uploads/
  },
  filename: (req, file, cb) => {
    cb(null, file.fieldname + '-' + Date.now() + path.extname(file.originalname)); // Penamaan file unik
  },
});

const upload = multer({
  storage: storage,
  fileFilter: (req, file, cb) => {
    const filetypes = /jpeg|jpg|png|gif/;
    const extname = filetypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = filetypes.test(file.mimetype);
    if (extname && mimetype) {
      return cb(null, true);
    } else {
      return cb(new Error('Hanya file gambar yang diizinkan'));
    }
  },
});

// API Key dan URL untuk Roboflow
const ROBOFLOW_API_KEY = 'Y2RhFee09f8bvvheivuV';
const ROBOFLOW_API_URL = 'https://detect.roboflow.com/pbkk-book-search/3'; // Ganti dengan model dan API Key yang benar

// Endpoint untuk upload gambar dan melakukan inferensi dengan Roboflow
app.post('/upload', upload.single('image'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ success: false, message: 'Tidak ada file yang diunggah' });
    }

    // Path file yang diupload
    const imagePath = req.file.path;

    // Baca file sebagai stream untuk dikirimkan ke API Roboflow
    const formData = new FormData();
    formData.append('file', fs.createReadStream(imagePath));

    // Kirim gambar ke API Roboflow menggunakan axios
    const response = await axios.post(ROBOFLOW_API_URL, formData, {
      params: {
        api_key: ROBOFLOW_API_KEY,
      },
      headers: {
        ...formData.getHeaders(), // Mendapatkan headers yang dibutuhkan oleh form-data
      },
    });

    // Mengembalikan hasil deteksi dari Roboflow
    res.json({
      success: true,
      result: response.data,
    });
  } catch (error) {
    console.error('Error:', error.response ? error.response.data : error.message);
    res.status(500).json({
      success: false,
      message: 'Gagal menghubungi Roboflow',
      error: error.message,
    });
  }
});

// Jalankan server Node.js pada port 3000
app.listen(port, () => {
  console.log(`Server berjalan di http://localhost:${port}`);
});
