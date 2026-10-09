<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(Exam $exam): View
    {
        return view('questions.create', compact('exam'));
    }

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($exam, $data) {
            $question = $exam->questions()->create([
                'text' => $data['text'],
                'position' => $exam->questions()->max('position') + 1,
            ]);

            $this->syncOptions($question, $data);
        });

        return redirect()
            ->route('exams.edit', $exam)
            ->with('status', 'Pregunta agregada.');
    }

    public function edit(Exam $exam, Question $question): View
    {
        abort_unless($question->exam_id === $exam->id, 404);

        $question->load('options');

        return view('questions.edit', compact('exam', 'question'));
    }

    public function update(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        abort_unless($question->exam_id === $exam->id, 404);

        $data = $this->validated($request);

        DB::transaction(function () use ($question, $data) {
            $question->update(['text' => $data['text']]);
            $question->options()->delete();
            $this->syncOptions($question, $data);
        });

        return redirect()
            ->route('exams.edit', $exam)
            ->with('status', 'Pregunta actualizada.');
    }

    public function destroy(Exam $exam, Question $question): RedirectResponse
    {
        abort_unless($question->exam_id === $exam->id, 404);

        $question->delete();

        return redirect()
            ->route('exams.edit', $exam)
            ->with('status', 'Pregunta eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*.text' => ['required', 'string', 'max:1000'],
            'correct' => ['required', 'integer'],
        ]);

        if (! array_key_exists($data['correct'], $data['options'])) {
            throw ValidationException::withMessages([
                'correct' => 'Debes marcar una respuesta correcta válida.',
            ]);
        }

        return $data;
    }

    private function syncOptions(Question $question, array $data): void
    {
        foreach (array_values($data['options']) as $index => $option) {
            $question->options()->create([
                'text' => $option['text'],
                'is_correct' => (int) $data['correct'] === $index,
                'position' => $index,
            ]);
        }
    }
}
