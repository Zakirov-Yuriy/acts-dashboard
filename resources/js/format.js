const rub = new Intl.NumberFormat('ru-RU', {
    style: 'currency', currency: 'RUB', maximumFractionDigits: 0,
});

export const money = (v) => rub.format(Number(v ?? 0));

export const date = (v) => {
    if (!v) return '—';
    const [y, m, d] = String(v).split('-');
    return `${d}.${m}.${y}`;
};
