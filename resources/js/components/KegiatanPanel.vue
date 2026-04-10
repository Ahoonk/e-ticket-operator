<template>
  <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-white">Daftar Kegiatan</h2>
        <p class="mt-1 text-sm text-slate-400">Kelola laporan gangguan jaringan.</p>
      </div>
      <div class="flex w-full flex-wrap gap-2 sm:w-auto">
        <button
          class="w-full rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500 sm:w-auto"
          @click="load"
        >
          Refresh
        </button>
        <button
          class="w-full rounded-lg bg-emerald-500 px-3 py-1 text-sm font-semibold text-slate-950 hover:bg-emerald-400 sm:w-auto"
          @click="emit('create')"
        >
          Buat Kegiatan
        </button>
      </div>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
      <div class="flex w-full flex-wrap gap-2 sm:w-auto">
        <input
          v-model="search"
          placeholder="Cari lokasi, jenis, kendala, tim, keterangan..."
          class="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 sm:w-64"
        />
        <select
          v-model="statusFilter"
          class="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-xs text-slate-100 sm:w-auto"
        >
          <option value="all">Semua Status</option>
          <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
        </select>
      </div>
      <p class="text-xs text-slate-400">Total: {{ filteredItems.length }}</p>
    </div>

    <div class="mt-3 max-w-full overflow-x-auto rounded-xl border border-slate-800">
      <table class="min-w-[700px] w-full border-collapse text-[11px] sm:text-xs">
        <thead class="bg-slate-900/80 text-slate-200">
          <tr>
            <th class="border border-slate-800 px-2 py-2 text-center">No</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Lokasi/OPD</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Jenis Gangguan</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Kendala</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Tim Bertugas</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Status</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td class="border border-slate-800 px-3 py-2 text-center text-slate-400" colspan="7">Memuat data...</td>
          </tr>
          <tr v-else-if="filteredItems.length === 0">
            <td class="border border-slate-800 px-3 py-2 text-center text-slate-400" colspan="7">Belum ada laporan.</td>
          </tr>
          <tr v-for="(item, index) in filteredItems" :key="item.id" class="odd:bg-slate-950/30">
            <td class="border border-slate-800 px-2 py-2 text-center">{{ index + 1 }}</td>
            <td class="border border-slate-800 px-2 py-2 text-left">{{ item.lokasi_opd || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.jenis_gangguan }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.kendala || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.tim_bertugas || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">
              <span class="inline-flex justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="statusClass(item.status)">
                {{ item.status || '-' }}
              </span>
            </td>
            <td class="border border-slate-800 px-2 py-2">
              <div class="flex flex-wrap justify-center gap-2">
                <button
                  class="rounded-lg border border-slate-700 px-2 py-1 text-[10px] text-slate-200 hover:border-slate-500"
                  @click="emit('edit', item)"
                  aria-label="Edit"
                  title="Edit"
                >
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M4 13.5V16h2.5l7.1-7.1-2.5-2.5L4 13.5z"></path>
                    <path d="M14.7 3.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-1.1 1.1-2.5-2.5 1.1-1.1z"></path>
                  </svg>
                </button>
                <button
                  class="rounded-lg border border-rose-500/40 px-2 py-1 text-[10px] text-rose-200 hover:border-rose-400"
                  @click="remove(item.id)"
                  aria-label="Hapus"
                  title="Hapus"
                >
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6 7h8l-1 9H7L6 7z"></path>
                    <path d="M8 4h4l1 1h3v2H4V5h3l1-1z"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { deleteGangguan, listGangguan } from '../services/gangguan';

const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['create', 'edit']);

const items = ref([]);
const loading = ref(false);
const error = ref(null);
const statusOptions = ['BELUM DIKERJAKAN', 'PROSES', 'SELESAI'];
const search = ref('');
const statusFilter = ref('all');

const filteredItems = computed(() => {
  const term = search.value.trim().toLowerCase();
  let result = items.value;

  if (statusFilter.value !== 'all') {
    result = result.filter((item) => (item.status || '').toLowerCase() === statusFilter.value.toLowerCase());
  }

  if (!term) return result;

  return result.filter((item) => {
    const haystack = [
      item.tanggal_gangguan,
      item.lokasi_opd,
      item.jenis_gangguan,
      item.mulai_pengerjaan,
      item.selesai_pengerjaan,
      item.kendala,
      item.tindak_lanjut,
      item.tim_bertugas,
      item.status,
      item.keterangan,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase();

    return haystack.includes(term);
  });
});

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    items.value = await listGangguan();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat data kegiatan.');
  } finally {
    loading.value = false;
  }
};

const remove = async (id) => {
  if (!confirm('Hapus kegiatan ini?')) return;
  try {
    await deleteGangguan(id);
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menghapus kegiatan.');
  }
};

const statusClass = (status) => {
  if (status === 'SELESAI') {
    return 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/40';
  }
  if (status === 'PROSES') {
    return 'bg-amber-400/20 text-amber-200 border border-amber-400/40';
  }
  return 'bg-rose-500/20 text-rose-200 border border-rose-400/40';
};

watch(() => props.refreshKey, load);

onMounted(load);
</script>
