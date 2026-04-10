import { ref } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { fetchUser, login, logout } from '../services/auth';

const user = ref(null);
const loading = ref(true);
const error = ref(null);

export function useAuth() {
    const init = async () => {
        loading.value = true;
        try {
            user.value = await fetchUser();
        } catch (err) {
            user.value = null;
        } finally {
            loading.value = false;
        }
    };

    const loginUser = async (payload) => {
        error.value = null;
        try {
            await login(payload);
            await init();
        } catch (err) {
            error.value = getErrorMessage(err, 'Gagal login. Periksa email dan password.');
        }
    };

    const logoutUser = async () => {
        error.value = null;
        try {
            await logout();
        } finally {
            user.value = null;
        }
    };

    return {
        user,
        loading,
        error,
        init,
        loginUser,
        logoutUser,
    };
}
