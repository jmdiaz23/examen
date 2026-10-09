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

window.Alpine = Alpine;

Alpine.start();
