import api from './api';

export async function listAnggota() {
  const { data } = await api.get('/anggota');
  return data;
}

export async function createAnggota(payload) {
  const { data } = await api.post('/anggota', payload);
  return data;
}

export async function updateAnggota(id, payload) {
  const { data } = await api.put(`/anggota/${id}`, payload);
  return data;
}

export async function deleteAnggota(id) {
  await api.delete(`/anggota/${id}`);
}
