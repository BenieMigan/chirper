<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
use Illuminate\Http\Response; 
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon; 





class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(): View
    {
        $dateLimit = Carbon::now()->subDays(7);
    
        $chirps = Chirp::with('user')
                    ->where('created_at', '>=', $dateLimit)
                    ->latest()
                    ->get();
    
        return view('chirps.index', [
            'chirps' => $chirps,
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Vérifier que l'utilisateur n'a pas plus de 10 chirps
        if ($request->user()->chirps()->count() >= 10) {
            return redirect(route('chirps.index'))->withErrors(['message' => 'Vous avez atteint la limite de 10 chirps.']);
        }
    
        // Valider et créer le chirp
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);
    
        $chirp = $request->user()->chirps()->create($validated);
    
        return redirect(route('chirps.index'))->with('status', 'Chirp created!')->setStatusCode(201);
    }
    

 

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp): View
    {
        //
        Gate::authorize('update', $chirp);
 
        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
{
    $this->authorize('update', $chirp);

    $validated = $request->validate([
        'message' => 'required|string|max:255',
    ]);

    $chirp->update($validated);

    return redirect(route('chirps.index'))->with('status', 'Chirp updated!');
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp): RedirectResponse
    {
        //
        Gate::authorize('delete', $chirp);
 
        $chirp->delete();
 
        return redirect(route('chirps.index'));
    }

    public function like(Chirp $chirp): RedirectResponse
{
    $user = auth()->user();

    // Vérifier si l'utilisateur a déjà liké ce chirp
    if ($chirp->likes()->where('user_id', $user->id)->exists()) {
        return redirect(route('chirps.index'))->withErrors(['message' => 'Vous avez déjà liké ce chirp.']);
    }

    // Créer un like
    $chirp->likes()->create(['user_id' => $user->id]);

    return redirect(route('chirps.index'))->with('status', 'Chirp liked!');
}

}
