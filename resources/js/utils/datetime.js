const DATE_TIME_PATTERN = /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::(\d{2}))?)?/;

const pad = (value) => String(value).padStart(2, '0');

export function parseDateTimeValue(value) {
    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    if (typeof value === 'number') {
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    if (value === null || value === undefined) {
        return null;
    }

    const text = String(value).trim();
    if (!text) {
        return null;
    }

    const hasExplicitTimezone = /([zZ]|[+-]\d{2}:?\d{2})$/.test(text);
    if (hasExplicitTimezone) {
        const timezoneDate = new Date(text);
        if (!Number.isNaN(timezoneDate.getTime())) {
            return timezoneDate;
        }
    }

    const match = text.replace(' ', 'T').match(DATE_TIME_PATTERN);
    if (match) {
        const [, year, month, day, hour = '00', minute = '00', second = '00'] = match;
        const date = new Date(
            Number(year),
            Number(month) - 1,
            Number(day),
            Number(hour),
            Number(minute),
            Number(second),
        );

        return Number.isNaN(date.getTime()) ? null : date;
    }

    const fallback = new Date(text);
    return Number.isNaN(fallback.getTime()) ? null : fallback;
}

function formatDatePart(date) {
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function formatTimePart(date) {
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

export function formatDateOnly(value, fallback = '-') {
    const date = parseDateTimeValue(value);
    if (!date) {
        return fallback;
    }

    return formatDatePart(date);
}

export function formatDateTime(value, fallback = '-') {
    const date = parseDateTimeValue(value);
    if (!date) {
        return fallback;
    }

    return `${formatDatePart(date)} ${formatTimePart(date)}`;
}

export function toDateTimeLocalValue(value) {
    const date = parseDateTimeValue(value);
    if (!date) {
        return '';
    }

    return [
        date.getFullYear(),
        pad(date.getMonth() + 1),
        pad(date.getDate()),
    ].join('-') + `T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}
