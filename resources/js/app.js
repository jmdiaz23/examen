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
    modal: false,
    modalType: null,
    modalTitle: '',
    modalMessage: '',
    modalConfirmText: 'Aceptar',
    get question() {
        return this.questions[this.current];
    },
    get total() {
        return this.questions.length;
    },
    get answeredCount() {
        return Object.keys(this.answers).length;
    },
    get allAnswered() {
        return this.questions.every((q) => this.answers[q.id]);
    },
    isAnswered(questionId) {
        return Boolean(this.answers[questionId]);
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
        if (this.current < this.total - 1 && this.isAnswered(this.question.id)) {
            this.current++;
        }
    },
    prev() {
        if (this.current > 0) this.current--;
    },
    goTo(index) {
        if (this.isAnswered(this.questions[index].id)) {
            this.current = index;
        }
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
            if (this.remaining <= 0) this.autoSubmit();
        }, 1000);
    },
    missingCount() {
        return this.questions.filter((q) => !this.answers[q.id]).length;
    },
    openMissing() {
        const missing = this.missingCount();
        this.modalType = 'missing';
        this.modalTitle = 'Faltan respuestas';
        this.modalMessage = `Debes responder todas las preguntas antes de enviar. Te faltan ${missing}.`;
        this.modalConfirmText = 'Entendido';
        this.modal = true;
    },
    openConfirm() {
        this.modalType = 'confirm';
        this.modalTitle = 'Enviar examen';
        this.modalMessage = '¿Seguro que quieres enviar el examen? No podrás volver a presentarlo.';
        this.modalConfirmText = 'Enviar ahora';
        this.modal = true;
    },
    openTimeUp() {
        this.modalType = 'sending';
        this.modalTitle = 'Tiempo agotado';
        this.modalMessage = 'Se acabó el tiempo. Estamos enviando tu examen...';
        this.modalConfirmText = 'Enviando...';
        this.modal = true;
    },
    closeModal() {
        if (this.modalType === 'sending' || this.submitting) return;
        this.modal = false;
    },
    modalConfirm() {
        if (this.modalType === 'missing') {
            this.modal = false;
            return;
        }
        if (this.modalType === 'confirm') {
            this.modal = false;
            this.doSubmit();
        }
    },
    doSubmit(auto = false) {
        if (this.submitting) return;
        this.submitting = true;
        document.getElementById('exam-autosubmit').value = auto ? '1' : '0';
        document.getElementById('exam-submit-form').submit();
    },
    finish() {
        if (this.submitting) return;

        if (this.missingCount() > 0) {
            this.openMissing();
            return;
        }

        this.openConfirm();
    },
    autoSubmit() {
        if (this.submitting) return;
        this.openTimeUp();
        setTimeout(() => this.doSubmit(true), 1500);
    },
});

window.Alpine = Alpine;

Alpine.start();
