<template>
  <section class="w-full">
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

    <div v-else class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="item in items"
        :key="item.id"
        class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/40 p-5 shadow-[0_0_0_1px_rgba(15,23,42,0.4)] transition hover:-translate-y-1 hover:border-slate-700"
      >
        <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"></div>
        <div class="flex items-center justify-between gap-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1 text-[11px] text-slate-200">
            <span class="h-2.5 w-2.5 rounded-full" :class="statusDot(item.status)"></span>
            {{ item.status || 'BELUM DIKERJAKAN' }}
          </span>
          <span class="text-xs text-slate-500">{{ formatDate(item.tanggal_gangguan) }}</span>
        </div>

        <h3 class="mt-4 text-base font-semibold text-white">
          {{ item.jenis_gangguan || 'Tanpa judul' }}
        </h3>
        <p class="mt-1 text-sm text-slate-400">
          {{ item.lokasi_opd || 'Lokasi belum diisi' }}
        </p>

        <div class="mt-4 grid gap-2 text-xs text-slate-400">
          <div>
            <span class="text-slate-500">Kendala:</span>
            <span class="ml-1 text-slate-200">{{ item.kendala || '-' }}</span>
          </div>
          <div>
            <span class="text-slate-500">Tim:</span>
            <span class="ml-1 text-slate-200">{{ item.tim_bertugas || '-' }}</span>
          </div>
        </div>
      </div>

      <div
        v-if="items.length === 0"
        class="rounded-2xl border border-dashed border-slate-800 bg-slate-950/30 p-6 text-center text-sm text-slate-400 sm:col-span-2 lg:col-span-3"
      >
        Belum ada kegiatan yang tersimpan.
      </div>
    </div>

    <div v-if="hasMore" class="mt-6 flex justify-center">
      <button
        class="rounded-full border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:border-slate-500"
        @click="loadMore"
      >
        Muat Lainnya
      </button>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { listGangguanPublic } from '../services/gangguan';

const items = ref([]);
const loading = ref(false);
const error = ref(null);
const page = ref(1);
const pageSize = 6;
const meta = ref(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    page.value = 1;
    const response = await listGangguanPublic({ page: page.value, limit: pageSize });
    items.value = response.data || [];
    meta.value = response.meta || null;
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat daftar kegiatan.');
  } finally {
    loading.value = false;
  }
};

const hasMore = computed(() => {
  if (!meta.value) return false;
  return meta.value.current_page < meta.value.last_page;
});

const loadMore = async () => {
  if (loading.value || !hasMore.value) return;
  loading.value = true;
  error.value = null;
  try {
    const nextPage = page.value + 1;
    const response = await listGangguanPublic({ page: nextPage, limit: pageSize });
    const nextItems = response.data || [];
    items.value = [...items.value, ...nextItems];
    page.value = nextPage;
    meta.value = response.meta || meta.value;
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat daftar kegiatan.');
  } finally {
    loading.value = false;
  }
};

const statusDot = (status) => {
  if (status === 'SELESAI') return 'bg-emerald-400';
  if (status === 'PROSES') return 'bg-amber-400';
  return 'bg-rose-400';
};

const formatDate = (value) => {
  if (!value) return '-';
  return value;
};

onMounted(load);
</script>
