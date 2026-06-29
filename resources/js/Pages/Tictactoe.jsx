Route::get('/tictactoe', function () {
    return Inertia::render('Tictactoe');
})->name('tictactoe');
