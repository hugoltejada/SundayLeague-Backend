<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Sube una imagen y devuelve la URL pública.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:20480', // máximo 20MB
        ]);

        // Guardamos en storage/app/public/uploads
        $path = $request->file('file')->store('uploads', 'public');

        return response()->json([
            'url' => asset(Storage::url($path)),
        ]);
    }
}
