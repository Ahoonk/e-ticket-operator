import api from './api';

export async function listKegiatan() {
    const { data } = await api.get('/kegiatan');
    return data;
}

export async function createKegiatan(payload) {
    const { data } = await api.post('/kegiatan', payload);
    return data;
}

export async function updateKegiatan(id, payload) {
    const { data } = await api.put(`/kegiatan/${id}`, payload);
    return data;
}

export async function deleteKegiatan(id) {
    await api.delete(`/kegiatan/${id}`);
}
