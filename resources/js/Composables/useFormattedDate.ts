import dayjs from 'dayjs';

export function useFormattedDate() {
    const formatDate = (date: string, format: string = 'DD-MM-YYYY HH:mm:ss') => dayjs(date).format(format);
    const formatDateWithoutTime = (date: string, format: string = 'DD-MM-YYYY') => dayjs(date).format(format);
    const formatDateMonthYear = (date: string, format: string = 'MMMM YYYY') => dayjs(date).format(format);
    return {
        formatDate,
        formatDateWithoutTime,
        formatDateMonthYear
    };
}