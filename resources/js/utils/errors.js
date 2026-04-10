export function getErrorMessage(error, fallback = 'Terjadi kesalahan. Coba lagi.') {
    if (error?.response?.data?.message) {
        return error.response.data.message;
    }

    const errors = error?.response?.data?.errors;
    if (errors && typeof errors === 'object') {
        return Object.values(errors).flat().join(' ');
    }

    return fallback;
}
