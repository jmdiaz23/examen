import './bootstrap';

import Alpine from 'alpinejs';

window.questionForm = (initial, correct) => ({
    options: Array.isArray(initial) && initial.length ? initial : [{ text: '' }, { text: '' }],
    correct: correct ?? 0,
    addOption() {
        if (this.options.length < 6) {
            this.options.push({ text: '' });
        }
    },
    removeOption(index) {
        if (this.options.length > 2) {
            this.options.splice(index, 1);
            if (this.correct >= this.options.length) {
                this.correct = 0;
            }
        }
    },
});

window.examRunner = (config) => ({
    questions: config.questions,
    answers: config.answers ?? {},
    current: 0,
    remaining: config.remaining,
    answerUrl: config.answerUrl,
    submitUrl: config.submitUrl,
    resultUrl: config.resultUrl,
    submitting: false,
    saving: false,
    saved: false,
    get question() {
        return this.questions[this.current];
    },
    get total() {
        return this.questions.length;
    },
    get answeredCount() {
        return Object.keys(this.answers).length;
    },
    isSelected(optionId) {
        return Number(this.answers[this.question.id]) === Number(optionId);
    },
    async selectOption(optionId) {
        if (this.submitting) return;
        this.answers[this.question.id] = optionId;
        this.saving = true;
        this.saved = false;
        try {
            const { data } = await window.axios.post(this.answerUrl, {
                question_id: this.question.id,
                option_id: optionId,
            });
            if (data && data.redirect) {
                window.location = data.redirect;
                return;
            }
            this.saved = true;
            setTimeout(() => (this.saved = false), 1500);
        } catch (error) {
            const redirect = error?.response?.data?.redirect;
            if (redirect) {
                window.location = redirect;
            }
        } finally {
            this.saving = false;
        }
    },
    next() {
        if (this.current < this.total - 1) this.current++;
    },
    prev() {
        if (this.current > 0) this.current--;
    },
    goTo(index) {
        this.current = index;
    },
    formatTime() {
        const minutes = Math.floor(this.remaining / 60);
        const seconds = this.remaining % 60;
        return `${minutes}:${String(seconds).padStart(2, '0')}`;
    },
    startTimer() {
        setInterval(() => {
            if (this.remaining <= 0) return;
            this.remaining--;
            if (this.remaining <= 0) this.finish();
        }, 1000);
    },
    finish() {
        if (this.submitting) return;
        if (!confirm('¿Seguro que quieres enviar el examen? No podrás volver a presentarlo.')) {
            if (this.remaining <= 0) {
                document.getElementById('exam-submit-form').submit();
            }
            return;
        }
        this.submitting = true;
        document.getElementById('exam-submit-form').submit();
    },
});

window.Alpine = Alpine;

Alpine.start();
