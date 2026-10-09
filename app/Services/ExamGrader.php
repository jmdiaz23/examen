<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Option;

class ExamGrader
{
    public function grade(Attempt $attempt, string $status = 'submitted'): void
    {
        $questionIds = $attempt->question_ids ?? [];

        $correctOptions = Option::whereIn('question_id', $questionIds)
            ->get()
            ->groupBy('question_id')
            ->map(fn ($options) => $options->firstWhere('is_correct', true)?->id);

        $answers = $attempt->answers()->get()->keyBy('question_id');

        $score = 0;

        foreach ($questionIds as $questionId) {
            $answer = $answers->get($questionId);

            if (! $answer) {
                continue;
            }

            $isCorrect = $answer->option_id
                && (int) $correctOptions->get($questionId) === (int) $answer->option_id;

            $answer->update(['is_correct' => $isCorrect]);

            if ($isCorrect) {
                $score++;
            }
        }

        $attempt->update([
            'score' => $score,
            'total_questions' => count($questionIds),
            'status' => $status,
            'submitted_at' => $attempt->submitted_at ?? now(),
        ]);
    }
}
