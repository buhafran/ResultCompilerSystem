<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Compiled = 'compiled';
    case Released = 'released';
    case Withdrawn = 'withdrawn';
}
