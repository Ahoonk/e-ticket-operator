import axios from 'axios';
import api, { csrf } from './api';

export async function fetchUser() {
    const { data } = await api.get('/user');
    return data;
}

export async function login(payload) {
    await csrf();
    await axios.post('/login', payload);
}

export async function logout() {
    await axios.post('/logout');
}
