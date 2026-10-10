<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Services\ExamGrader;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicExamController extends Controller
{
    public function __construct(private readonly ExamGrader $grader) {}

    public function show(Exam $exam): View
    {
        return view('public.show', compact('exam'));
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        if (! $exam->is_active) {
            return redirect()->route('public.exam.show', $exam);
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));

        if ($exam->attempts()->where('email', $email)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Este correo ya presentó este examen. Solo se permite un intento.']);
        }

        $questionIds = $exam->questions()->pluck('id')->all();

        if (count($questionIds) === 0) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Este examen todavía no tiene preguntas disponibles.']);
        }

        if ($exam->shuffle_questions) {
            shuffle($questionIds);
        }

        $questionIds = array_slice($questionIds, 0, min($exam->questions_to_show, count($questionIds)));

        try {
            $attempt = $exam->attempts()->create([
                'full_name' => $data['full_name'],
                'semester' => $data['semester'],
                'email' => $email,
                'started_at' => now(),
                'expires_at' => now()->addMinutes($exam->duration_minutes),
                'status' => 'started',
                'total_questions' => count($questionIds),
                'question_ids' => $questionIds,
                'ip_address' => $request->ip(),
            ]);
        } catch (QueryException) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Este correo ya presentó este examen. Solo se permite un intento.']);
        }

        return redirect()->route('public.exam.take', [$exam, $attempt]);
    }

    public function take(Exam $exam, Attempt $attempt): View|RedirectResponse
    {
        $this->ensureOwnership($exam, $attempt);

        if ($attempt->isFinished()) {
            return redirect()->route('public.exam.result', [$exam, $attempt]);
        }

        if ($attempt->isExpired()) {
            $this->grader->grade($attempt, 'expired');

            return redirect()->route('public.exam.result', [$exam, $attempt]);
        }

        $questions = Question::with('options')
            ->whereIn('id', $attempt->question_ids ?? [])
            ->get()
            ->keyBy('id');

        $ordered = collect($attempt->question_ids ?? [])
            ->map(fn ($id) => $questions->get($id))
            ->filter()
            ->values();

        if ($exam->shuffle_options) {
            $ordered->each(fn (Question $question) => $question->setRelation('options', $question->options->shuffle()->values()));
        }

        $answers = $attempt->answers()->pluck('option_id', 'question_id');

        $config = [
            'questions' => $ordered->map(fn (Question $question) => [
                'id' => $question->id,
                'text' => $question->text,
                'options' => $question->options->map(fn (Option $option) => [
                    'id' => $option->id,
                    'text' => $option->text,
                ])->values(),
            ])->values(),
            'answers' => $answers,
            'remaining' => $attempt->remainingSeconds(),
            'answerUrl' => route('public.exam.answer', [$exam, $attempt]),
            'submitUrl' => route('public.exam.submit', [$exam, $attempt]),
            'resultUrl' => route('public.exam.result', [$exam, $attempt]),
        ];

        return view('public.take', compact('exam', 'attempt', 'config'));
    }

    public function answer(Request $request, Exam $exam, Attempt $attempt): JsonResponse
    {
        $this->ensureOwnership($exam, $attempt);

        if ($attempt->isExpired() && ! $attempt->isFinished()) {
            $this->grader->grade($attempt, 'expired');
        }

        if ($attempt->isFinished()) {
            return response()->json([
                'status' => 'finished',
                'redirect' => route('public.exam.result', [$exam, $attempt]),
            ], 409);
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['required', 'integer'],
        ]);

        $questionIds = array_map('intval', $attempt->question_ids ?? []);

        abort_unless(in_array((int) $data['question_id'], $questionIds, true), 422);

        $option = Option::where('id', $data['option_id'])
            ->where('question_id', $data['question_id'])
            ->firstOrFail();

        $attempt->answers()->updateOrCreate(
            ['question_id' => $data['question_id']],
            [
                'option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'remaining' => $attempt->fresh()->remainingSeconds(),
        ]);
    }

    public function submit(Request $request, Exam $exam, Attempt $attempt): RedirectResponse
    {
        $this->ensureOwnership($exam, $attempt);

        $auto = $request->boolean('auto');

        if (! $auto && ! $attempt->isFinished()) {
            $questionIds = $attempt->question_ids ?? [];
            $answered = $attempt->answers()->whereIn('question_id', $questionIds)->count();
            $total = count($questionIds);

            if ($answered < $total) {
                return redirect()
                    ->route('public.exam.take', [$exam, $attempt])
                    ->withErrors(['respuestas' => 'Debes responder todas las preguntas antes de enviar. Faltan ' . ($total - $answered) . '.']);
            }
        }

        if (! $attempt->isFinished()) {
            $this->grader->grade($attempt, $attempt->isExpired() ? 'expired' : 'submitted');
        }

        return redirect()->route('public.exam.result', [$exam, $attempt]);
    }

    public function result(Exam $exam, Attempt $attempt): View|RedirectResponse
    {
        $this->ensureOwnership($exam, $attempt);

        if (! $attempt->isFinished()) {
            return redirect()->route('public.exam.take', [$exam, $attempt]);
        }

        return view('public.result', compact('exam', 'attempt'));
    }

    private function ensureOwnership(Exam $exam, Attempt $attempt): void
    {
        abort_unless($attempt->exam_id === $exam->id, 404);
    }
}
