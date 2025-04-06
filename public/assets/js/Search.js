// Search.js — простой класс для фронтового фильтра по карточкам
export class Search {
    constructor(inputSelector, cardSelector, attribute = 'data-search') {
        this.input = document.querySelector(inputSelector);
        this.cards = document.querySelectorAll(cardSelector);
        this.attribute = attribute;

        if (this.input) {
            this.input.addEventListener('input', () => this.filter());
        }
    }

    filter() {
        const term = this.input.value.trim().toLowerCase();

        this.cards.forEach(card => {
            const value = (card.getAttribute(this.attribute) || '').toLowerCase();
            const match = value.includes(term);
            card.style.display = match ? '' : 'none';
        });
    }
}
