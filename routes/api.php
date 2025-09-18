use App\Http\Controllers\Api\PhoneController;

Route::prefix('phone')->group(function () {
Route::post('/phone/registry', [PhoneController::class, 'register']);
Route::post('/phone/check', [PhoneController::class, 'verify']);
});