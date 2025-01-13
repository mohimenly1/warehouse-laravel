<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
{
    $packages = Package::all();
    return response()->json($packages);
}
}
