use App\Http\Controllers\Api\PhoneController;


Route::post('phone/register-email', [PhoneController::class, 'registerEmail'])
->middleware('phone.key');

Route::post('phone/verify-email', [PhoneController::class, 'verifyEmail'])
->middleware('phone.key');

Route::post('phone/register-google', [PhoneController::class, 'registerGoogle'])
->middleware('phone.key');