<?php declare(strict_types=1);

namespace App\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function show(): Response
    {
        return view('user.profile', []);
    }
}
