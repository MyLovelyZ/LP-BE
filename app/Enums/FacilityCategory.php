<?php

namespace App\Enums;

/**
 * Praktik = a major's practice room, Penunjang = a support facility shared by every student.
 */
enum FacilityCategory: string
{
    case Praktik = 'praktik';
    case Penunjang = 'penunjang';
}
