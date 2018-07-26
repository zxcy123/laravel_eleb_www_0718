<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Member extends User
{
    //
    use Notifiable;
    protected $guarded = [];
}
