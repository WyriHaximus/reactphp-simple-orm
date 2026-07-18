<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

enum JointType: string
{
    case INNER = 'INNER';
    case LEFT  = 'LEFT';
}
