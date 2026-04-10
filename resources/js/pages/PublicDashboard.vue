<template>
  <section class="rounded-2xl border border-slate-800 bg-slate-950/40 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold text-white">Daftar Gangguan Jaringan</h2>
        <p class="mt-1 text-sm text-slate-400">
          Data gangguan tampil di sini. Input hanya tersedia setelah login.
        </p>
      </div>
      <button
        class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500"
        @click="load"
      >
        Refresh
      </button>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-800">
      <table class="min-w-[1200px] w-full border-collapse text-xs">
        <thead class="bg-slate-900/80 text-slate-200">
          <tr>
            <th class="border border-slate-800 px-2 py-2 text-left">No</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Tanggal Gangguan</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Lokasi/OPD</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Jenis Gangguan</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Mulai Pengerjaan</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Selesai Pekerjaan</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Kendala</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Tindak Lanjut/Solusi</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Tim Bertugas</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Status</th>
            <th class="border border-slate-800 px-2 py-2 text-left">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td class="border border-slate-800 px-3 py-2 text-slate-400" colspan="11">Memuat data...</td>
          </tr>
          <tr v-else-if="items.length === 0">
            <td class="border border-slate-800 px-3 py-2 text-slate-400" colspan="11">Belum ada laporan.</td>
          </tr>
          <tr v-for="(item, index) in items" :key="item.id" class="odd:bg-slate-950/30">
            <td class="border border-slate-800 px-2 py-2">{{ index + 1 }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.tanggal_gangguan || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.lokasi_opd }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.jenis_gangguan }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.mulai_pengerjaan || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.selesai_pengerjaan || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.kendala || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.tindak_lanjut || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">{{ item.tim_bertugas || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">
              <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="statusClass(item.status)">
                {{ item.status || '-' }}
              </span>
            </td>
            <td class="border border-slate-800 px-2 py-2">{{ item.keterangan || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { listGangguan } from '../services/gangguan';
import { getErrorMessage } from '../utils/errors';

const items = ref([]);
const loading = ref(false);
const error = ref(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    items.value = await listGangguan();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat data gangguan.');
  } finally {
    loading.value = false;
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

onMounted(load);
</script>
