<script>
window.MaraNumberInputs = {
    QTY_DECIMALS: 3,
    MONEY_DECIMALS: 2,
    ARROW_STEP: 1,

    /** Acepta "1,5" o "1.5" */
    toNumber(str) {
        if (str === null || str === undefined) return NaN;
        const cleaned = String(str).trim().replace(/\s/g, '').replace(',', '.');
        if (cleaned === '' || cleaned === '.') return NaN;
        const v = parseFloat(cleaned);
        return Number.isFinite(v) ? v : NaN;
    },

    /** Para cálculos: vacío o inválido = 0 */
    qtyValue(str) {
        const v = this.toNumber(str);
        return Number.isFinite(v) && v > 0 ? v : 0;
    },

    moneyValue(str) {
        const v = this.toNumber(str);
        return Number.isFinite(v) && v >= 0 ? v : 0;
    },

    /** Cantidad: 1.00 por defecto, hasta 1.000 si hace falta */
    formatQty(value) {
        const v = this.toNumber(value);
        if (!Number.isFinite(v)) return '';
        const rounded = Math.round(v * 1000) / 1000;
        const thousand = Math.round(Math.abs(rounded) * 1000);
        if (thousand % 10 !== 0) return rounded.toFixed(3);
        return rounded.toFixed(2);
    },

    parseQty(value) {
        return this.qtyValue(value);
    },

    /** Precios: siempre 2 decimales (135.00) */
    formatMoney(value) {
        const v = this.toNumber(value);
        if (!Number.isFinite(v)) return '';
        return Math.max(0, v).toFixed(this.MONEY_DECIMALS);
    },

    bindArrowStep($input, { step = 1, formatter = null, emptyBase = 0 } = {}) {
        $input.off('keydown.maraArrow').on('keydown.maraArrow', function(e) {
            if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') return;
            e.preventDefault();
            const delta = e.key === 'ArrowUp' ? step : -step;
            let current = window.MaraNumberInputs.toNumber(this.value);
            if (!Number.isFinite(current)) current = emptyBase;
            let next = current + delta;
            if (next < 0) next = 0;
            this.value = formatter ? formatter(next) : String(next);
            $(this).trigger('input');
        });
    },

    enhanceQty($input) {
        const self = this;
        $input.attr({ type: 'text', inputmode: 'decimal', autocomplete: 'off' });
        $input.removeAttr('min step');

        this.bindArrowStep($input, {
            step: this.ARROW_STEP,
            emptyBase: 0,
            formatter: (v) => self.formatQty(v > 0 ? v : 1),
        });

        $input.off('blur.maraQty').on('blur.maraQty', function() {
            const raw = String(this.value).trim();
            if (raw === '') return;
            this.value = self.formatQty(raw);
            $(this).trigger('input');
        });
    },

    enhanceMoney($input) {
        const self = this;
        $input.attr({ type: 'text', inputmode: 'decimal', autocomplete: 'off' });
        $input.removeAttr('min step');

        this.bindArrowStep($input, {
            step: this.ARROW_STEP,
            emptyBase: 0,
            formatter: (v) => self.formatMoney(v),
        });

        $input.off('blur.maraMoney').on('blur.maraMoney', function() {
            const raw = String(this.value).trim();
            if (raw === '') return;
            this.value = self.formatMoney(raw);
            $(this).trigger('input');
        });
    },

    enhanceScope($scope, qtySelector, moneySelectors) {
        $scope.find(qtySelector).each((_, el) => this.enhanceQty($(el)));
        moneySelectors.forEach((sel) => {
            $scope.find(sel).each((_, el) => this.enhanceMoney($(el)));
        });
    },

    /** Normalizar filas antes de enviar el formulario */
    normalizeFormQty($form) {
        $form.find('.t-qty').each((_, el) => {
            const raw = String(el.value).trim();
            if (raw !== '') el.value = this.formatQty(raw);
        });
    },

    normalizeFormMoney($form, selectors) {
        selectors.forEach((sel) => {
            $form.find(sel).each((_, el) => {
                const raw = String(el.value).trim();
                if (raw !== '') el.value = this.formatMoney(raw);
            });
        });
    },
};
</script>
