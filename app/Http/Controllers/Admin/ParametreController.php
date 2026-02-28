<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ParametreController extends Controller
{
    public function edit(): View
    {
        return view('admin.parametres');
    }

    public function updateEmail(UpdateEmailRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'email' => strtolower(trim((string) $request->validated('email'))),
        ]);

        return redirect()->route('admin.parametres.edit')->with('succes', 'Adresse email mise a jour avec succes.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'password' => (string) $request->validated('nouveau_mot_de_passe'),
        ]);

        return redirect()->route('admin.parametres.edit')->with('succes', 'Mot de passe mis a jour avec succes.');
    }
}
