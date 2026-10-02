<?php

namespace App\Enums;

/**
 * News categories. Values must match `newsCategories` in the frontend (src/data/news.ts).
 */
enum NewsCategory: string
{
    case KegiatanSekolah = 'Kegiatan Sekolah';
    case Prestasi = 'Prestasi';
    case Pengumuman = 'Pengumuman';
    case KemitraanKerjaSama = 'Kemitraan & Kerja Sama';
    case KaryaInovasiSiswa = 'Karya & Inovasi Siswa';
    case ArtikelEdukasi = 'Artikel & Edukasi';
    case Alumni = 'Alumni';
}
