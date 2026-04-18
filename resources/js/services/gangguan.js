import api from './api';

export async function listGangguan() {
    const { data } = await api.get('/gangguan');
    return data;
}

export async function getGangguan(id) {
    const { data } = await api.get(`/gangguan/${id}`);
    return data;
}

export async function listGangguanPublic(params = {}) {
    const { data } = await api.get('/gangguan-public', { params });
    return data;
}

export async function createGangguan(payload) {
    const { data } = await api.post('/gangguan', payload);
    return data;
}

export async function updateGangguan(id, payload) {
    const { data } = await api.put(`/gangguan/${id}`, payload);
    return data;
}

export async function deleteGangguan(id) {
    await api.delete(`/gangguan/${id}`);
}

export async function completeGangguan(id, payload) {
    const { data } = await api.post(`/gangguan/${id}/complete`, payload);
    return data;
}
