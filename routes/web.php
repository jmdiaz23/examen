<?php

use App\Http\Controllers\ExamController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::prefix('examen/{exam}')->group(function () {
    Route::get('/', [PublicExamController::class, 'show'])->name('public.exam.show');
    Route::post('iniciar', [PublicExamController::class, 'start'])->name('public.exam.start');
    Route::get('intento/{attempt}', [PublicExamController::class, 'take'])->name('public.exam.take');
    Route::post('intento/{attempt}/responder', [PublicExamController::class, 'answer'])->name('public.exam.answer');
    Route::post('intento/{attempt}/enviar', [PublicExamController::class, 'submit'])->name('public.exam.submit');
    Route::get('intento/{attempt}/resultado', [PublicExamController::class, 'result'])->name('public.exam.result');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ExamController::class, 'index'])->name('dashboard');

    Route::resource('exams', ExamController::class)->except(['show']);

    Route::get('exams/{exam}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('exams/{exam}/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('exams/{exam}/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('exams/{exam}/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('exams/{exam}/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    Route::get('exams/{exam}/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('exams/{exam}/results/{attempt}', [ResultController::class, 'show'])->name('results.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
