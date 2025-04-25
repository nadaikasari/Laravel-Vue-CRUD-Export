export function useFormattedCurrency() {
    const formatCurrency = (value: number | string) => {
        const number = typeof value === 'string' ? parseFloat(value) : value;
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(number);
    };

    return {
        formatCurrency
    };
}
