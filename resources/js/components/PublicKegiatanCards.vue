<template>
  <section class="flex min-h-0 flex-1 flex-col">
    <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-white">Kegiatan Terbaru</h2>
        <p class="mt-1 text-sm text-slate-400">Ringkasan kegiatan yang sudah dibuat.</p>
      </div>
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        <button
          class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500"
          @click="load"
        >
          Refresh
        </button>
        <slot name="action" />
      </div>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <div v-if="loading" class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/40 p-6 text-sm text-slate-300">
      Memuat daftar kegiatan...
    </div>

    <div class="mt-6 min-h-0 flex-1 overflow-y-auto pr-1">
      <div v-if="sortedItems.length === 0" class="rounded-2xl border border-dashed border-slate-800 bg-slate-950/30 p-6 text-center text-sm text-slate-400">
        Belum ada kegiatan yang tersimpan.
      </div>

      <div v-else class="grid gap-4 pb-2 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="item in sortedItems"
          :key="item.id"
          class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/40 p-5 shadow-[0_0_0_1px_rgba(15,23,42,0.4)] transition hover:-translate-y-1 hover:border-slate-700"
        >
          <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"></div>
          <div class="flex items-center justify-between gap-2">
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1 text-[11px] text-slate-200">
              <span class="h-2.5 w-2.5 rounded-full" :class="statusDot(item.status)"></span>
              {{ item.status || 'BELUM DIKERJAKAN' }}
            </span>
            <span class="text-xs text-slate-500">{{ formatDateTime(item.tanggal_gangguan) }}</span>
          </div>

          <h3 class="mt-4 text-base font-semibold text-white">
            {{ item.jenis_gangguan || 'Tanpa judul' }}
          </h3>
          <p class="mt-1 text-sm text-slate-400">
            {{ item.lokasi_opd || 'Lokasi belum diisi' }}
          </p>
          <div class="mt-2 flex items-center justify-between gap-3 text-xs text-slate-500">
            <span>Tanggal input:</span>
            <span class="text-slate-300">{{ formatDateTime(item.tanggal_gangguan) }}</span>
          </div>

          <div class="mt-4 grid gap-2 text-xs text-slate-400">
            <div>
              <span class="text-slate-500">Keterangan:</span>
              <span class="ml-1 text-slate-200">{{ item.keterangan || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-500">Tim:</span>
              <span class="ml-1 text-slate-200">{{ formatTeamMembers(item.tim_bertugas) || '-' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { listGangguanPublic } from '../services/kegiatan-jaringan';
import { formatDateTime, parseDateTimeValue } from '../utils/datetime';

const items = ref([]);
const loading = ref(false);
const error = ref(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await listGangguanPublic();
    items.value = response || [];
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat daftar kegiatan.');
  } finally {
    loading.value = false;
  }
};

const statusPriority = (status) => {
  const value = (status || '').toUpperCase();
  if (value === 'PROSES') return 0;
  if (value === 'BELUM DIKERJAKAN') return 1;
  if (value === 'SELESAI') return 2;
  return 3;
};

const sortedItems = computed(() => {
  return [...items.value].sort((a, b) => {
    const statusDiff = statusPriority(a.status) - statusPriority(b.status);
    if (statusDiff !== 0) return statusDiff;

    const dateA = parseDateTimeValue(a.tanggal_gangguan)?.getTime() || 0;
    const dateB = parseDateTimeValue(b.tanggal_gangguan)?.getTime() || 0;
    if (dateA !== dateB) return dateB - dateA;

    return (b.id || 0) - (a.id || 0);
  });
});

const statusDot = (status) => {
  if (status === 'SELESAI') return 'bg-emerald-400';
  if (status === 'PROSES') return 'bg-amber-400';
  return 'bg-rose-400';
};

const formatTeamMembers = (value) => {
  if (value === null || value === undefined || value === '') return '';

  return String(value)
    .split(',')
    .map((part) => {
      const token = part.trim();
      if (!token) return '';

      if (token.includes('::')) {
        return token.split('::')[0].trim();
      }

      return token;
    })
    .filter(Boolean)
    .join(', ');
};

onMounted(load);
</script>
