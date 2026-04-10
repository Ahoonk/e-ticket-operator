import api from './api';

export async function listOpd() {
  const { data } = await api.get('/opd');
  return data;
}

export async function createOpd(payload) {
  const { data } = await api.post('/opd', payload);
  return data;
}

export async function updateOpd(id, payload) {
  const { data } = await api.put(`/opd/${id}`, payload);
  return data;
}

export async function deleteOpd(id) {
  await api.delete(`/opd/${id}`);
}
