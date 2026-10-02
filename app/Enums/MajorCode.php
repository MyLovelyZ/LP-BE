<?php

namespace App\Enums;

/**
 * Major codes. Values must match `code` in the frontend (src/data/majors.ts).
 */
enum MajorCode: string
{
    case Multimedia = 'MM';
    case RekayasaPerangkatLunak = 'RPL';
    case TeknikKomputerJaringan = 'TKJ';
    case PerbankanKeuanganMikro = 'PKM';
    case TeknikOtomasiIndustri = 'TOI';
}
