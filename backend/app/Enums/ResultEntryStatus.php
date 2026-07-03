<?php

namespace App\Enums;

enum ResultEntryStatus: string
{
    case NotEntered = 'not_entered';
    case Present = 'present';
    case Absent = 'absent';
}
