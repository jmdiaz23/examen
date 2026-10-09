<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::withCount(['questions', 'attempts'])
            ->latest()
            ->get();

        return view('exams.index', compact('exams'));
    }

    public function create(): View
    {
        return view('exams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $exam = Exam::create($data);

        return redirect()
            ->route('exams.edit', $exam)
            ->with('status', 'Examen creado. Ahora agrega las preguntas.');
    }

    public function edit(Exam $exam): View
    {
        $exam->load(['questions.options']);

        return view('exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $this->validated($request);

        $exam->update($data);

        return redirect()
            ->route('exams.edit', $exam)
            ->with('status', 'Examen actualizado.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()
            ->route('exams.index')
            ->with('status', 'Examen eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'questions_to_show' => ['required', 'integer', 'min:1', 'max:200'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:200'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'show_correct_answers' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['shuffle_questions'] = $request->boolean('shuffle_questions');
        $data['shuffle_options'] = $request->boolean('shuffle_options');
        $data['show_correct_answers'] = $request->boolean('show_correct_answers');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
