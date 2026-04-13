import api from './api';

export async function listDokumen() {
  const { data } = await api.get('/dokumen');
  return data;
}

export async function listGangguanDokumen(gangguanId) {
  const { data } = await api.get(`/gangguan/${gangguanId}/dokumen`);
  return data;
}

export async function uploadGangguanDokumen(gangguanId, formData) {
  const { data } = await api.post(`/gangguan/${gangguanId}/dokumen`, formData);
  return data;
}
