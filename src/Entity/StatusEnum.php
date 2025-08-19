<?php 

namespace App\Entity;

enum StatusEnum: string
{
    case EN_RETARD = 'en retard';
    case EN_COURS = 'en cours';
    case TERMINE = 'terminé';
}