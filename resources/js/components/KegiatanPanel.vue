<template>
  <div class="flex h-full min-h-0 flex-col rounded-2xl border border-slate-800 bg-slate-950/40 p-3 sm:p-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
      <div>
        <h2 class="text-base font-semibold text-white sm:text-lg">{{ canManage ? 'Daftar Kegiatan' : 'Kegiatan Saya' }}</h2>
        <p class="mt-1 text-xs text-slate-400 sm:text-sm">
          {{ canManage ? 'Kelola laporan gangguan jaringan.' : 'Lihat kegiatan yang ditugaskan kepada Anda.' }}
        </p>
      </div>
      <div class="flex w-full flex-wrap gap-2 sm:w-auto">
        <button
          type="button"
          class="w-full rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:border-slate-500 sm:w-auto sm:py-1"
          @click="load"
        >
          Refresh
        </button>
        <button
          v-if="canManage"
          type="button"
          class="w-full rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400 sm:w-auto sm:py-1"
          @click="emit('create')"
        >
          Buat Kegiatan
        </button>
      </div>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <div class="mt-4 flex flex-col gap-3 sm:mt-5 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
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
      <p class="text-xs text-slate-400 sm:text-right">Total: {{ filteredItems.length }}</p>
    </div>

    <div v-if="loading" class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/40 p-6 text-sm text-slate-300">
      Memuat data...
    </div>

    <div class="mt-4 min-h-0 flex-1 overflow-hidden">
      <div
        v-if="filteredItems.length === 0"
        class="rounded-2xl border border-dashed border-slate-800 bg-slate-950/30 p-4 text-center text-sm text-slate-400 sm:p-6"
      >
        Belum ada laporan.
      </div>

      <div v-else class="h-full overflow-y-auto pr-1">
        <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
          <div
            v-for="(item, index) in filteredItems"
            :key="item.id"
            class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/40 p-4 shadow-[0_0_0_1px_rgba(15,23,42,0.4)] transition hover:-translate-y-1 hover:border-slate-700 sm:p-5"
          >
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"></div>
            <div class="flex items-center justify-between gap-2">
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1 text-[11px] text-slate-200">
                <span class="h-2.5 w-2.5 rounded-full" :class="statusDot(item.status)"></span>
                {{ item.status || 'BELUM DIKERJAKAN' }}
              </span>
              <span class="text-xs text-slate-500 sm:text-xs">No {{ index + 1 }}</span>
            </div>

            <h3 class="mt-4 text-sm font-semibold text-white sm:text-base">
              {{ item.jenis_gangguan || 'Tanpa judul' }}
            </h3>
            <p class="mt-1 text-xs text-slate-400 sm:text-sm">
              {{ item.lokasi_opd || '-' }}
            </p>

            <div class="mt-3 grid gap-2 text-[11px] text-slate-400 sm:mt-4 sm:text-xs">
              <div>
                <span class="text-slate-500">Kendala:</span>
                <span class="ml-1 text-slate-200">{{ item.kendala || '-' }}</span>
              </div>
              <div>
                <span class="text-slate-500">Tim:</span>
                <span class="ml-1 text-slate-200">{{ item.tim_bertugas || '-' }}</span>
              </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-2 sm:mt-4">
              <button
                type="button"
                class="rounded-lg border border-slate-700 px-2 py-1 text-[10px] text-slate-200 hover:border-slate-500"
                @click="openView(item)"
                aria-label="Lihat"
                title="Lihat"
              >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M10 4.5c-4.5 0-8 4.5-8 5.5s3.5 5.5 8 5.5 8-4.5 8-5.5-3.5-5.5-8-5.5zm0 9a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z"></path>
                  <circle cx="10" cy="10" r="2"></circle>
                </svg>
              </button>

              <template v-if="canManage">
                <button
                  type="button"
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
                  type="button"
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
              </template>

              <button
                v-else-if="canComplete && item.status === 'PROSES'"
                type="button"
                class="w-full rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
                @click="openComplete(item)"
              >
                Selesai
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <teleport to="body">
      <div
        v-if="showCompleteModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 px-4"
        @click.self="closeComplete"
      >
        <div class="w-full max-w-xl rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-2xl">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold text-white">Selesaikan Kegiatan</h3>
            <p class="mt-1 text-sm text-slate-400">
              Isi kendala, tindak lanjut/solusi, dan keterangan sebelum status diubah menjadi selesai.
            </p>
          </div>
          <button
            type="button"
            class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-200 hover:border-slate-500"
            @click="closeComplete"
          >
            Tutup
          </button>
        </div>

        <div v-if="completeError" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
          {{ completeError }}
        </div>

        <form class="mt-5 grid gap-4" @submit.prevent="submitComplete">
          <label class="grid gap-2 text-sm text-slate-300">
            Kendala
            <textarea v-model="completeForm.kendala" class="min-h-24 rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
          </label>
          <label class="grid gap-2 text-sm text-slate-300">
            Tindak Lanjut/Solusi
            <textarea v-model="completeForm.tindak_lanjut" class="min-h-24 rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
          </label>
          <label class="grid gap-2 text-sm text-slate-300">
            Keterangan
            <textarea v-model="completeForm.keterangan" class="min-h-24 rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
          </label>

          <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-4">
            <button
              type="submit"
              class="w-full rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 sm:w-auto"
            >
              {{ completeLoading ? 'Menyimpan...' : 'Submit & Selesai' }}
            </button>
            <button
              type="button"
              class="w-full rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-200 hover:border-slate-500 sm:w-auto"
              @click="closeComplete"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
      </div>

      <div
        v-if="showViewModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 px-4"
        @click.self="closeView"
      >
        <div class="w-full max-w-2xl rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-2xl">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold text-white">Detail Kegiatan</h3>
            <p class="mt-1 text-sm text-slate-400">
              Informasi lengkap kegiatan yang sudah diinput.
            </p>
          </div>
          <button
            type="button"
            class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-200 hover:border-slate-500"
            @click="closeView"
          >
            Tutup
          </button>
        </div>

        <div v-if="viewItem" class="mt-5 grid gap-4 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4 sm:col-span-2">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</p>
            <div class="mt-2 flex items-center justify-between gap-3">
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-200">
                <span class="h-2.5 w-2.5 rounded-full" :class="statusDot(viewItem.status)"></span>
                {{ viewItem.status || 'BELUM DIKERJAKAN' }}
              </span>
              <span class="text-sm text-slate-400">{{ formatDate(viewItem.tanggal_gangguan) }}</span>
            </div>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Jenis Gangguan</p>
            <p class="mt-2 text-sm text-white">{{ viewItem.jenis_gangguan || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Lokasi/OPD</p>
            <p class="mt-2 text-sm text-white">{{ viewItem.lokasi_opd || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Mulai Pengerjaan</p>
            <p class="mt-2 text-sm text-white">{{ viewItem.mulai_pengerjaan || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Selesai Pengerjaan</p>
            <p class="mt-2 text-sm text-white">{{ viewItem.selesai_pengerjaan || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Kendala</p>
            <p class="mt-2 text-sm text-white whitespace-pre-wrap">{{ viewItem.kendala || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tindak Lanjut/Solusi</p>
            <p class="mt-2 text-sm text-white whitespace-pre-wrap">{{ viewItem.tindak_lanjut || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tim Bertugas</p>
            <p class="mt-2 text-sm text-white whitespace-pre-wrap">{{ viewItem.tim_bertugas || '-' }}</p>
          </div>

          <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Keterangan</p>
            <p class="mt-2 text-sm text-white whitespace-pre-wrap">{{ viewItem.keterangan || '-' }}</p>
          </div>
        </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { completeGangguan, deleteGangguan, listGangguan } from '../services/gangguan';

const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0,
  },
  canManage: {
    type: Boolean,
    default: true,
  },
  canComplete: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['create', 'edit']);

const items = ref([]);
const loading = ref(false);
const error = ref(null);
const statusOptions = ['BELUM DIKERJAKAN', 'PROSES', 'SELESAI'];
const search = ref('');
const statusFilter = ref('all');
const showViewModal = ref(false);
const viewItem = ref(null);
const showCompleteModal = ref(false);
const selectedItem = ref(null);
const completeLoading = ref(false);
const completeError = ref(null);
const completeForm = ref({
  kendala: '',
  tindak_lanjut: '',
  keterangan: '',
});

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

const openView = (item) => {
  viewItem.value = item;
  showViewModal.value = true;
};

const closeView = () => {
  showViewModal.value = false;
  viewItem.value = null;
};

const openComplete = (item) => {
  selectedItem.value = item;
  completeError.value = null;
  completeForm.value = {
    kendala: item.kendala || '',
    tindak_lanjut: item.tindak_lanjut || '',
    keterangan: item.keterangan || '',
  };
  showCompleteModal.value = true;
};

const closeComplete = () => {
  showCompleteModal.value = false;
  selectedItem.value = null;
  completeLoading.value = false;
  completeError.value = null;
  completeForm.value = {
    kendala: '',
    tindak_lanjut: '',
    keterangan: '',
  };
};

const submitComplete = async () => {
  if (!selectedItem.value) return;

  completeLoading.value = true;
  completeError.value = null;
  try {
    await completeGangguan(selectedItem.value.id, { ...completeForm.value });
    closeComplete();
    await load();
  } catch (err) {
    completeError.value = getErrorMessage(err, 'Gagal menyelesaikan kegiatan.');
  } finally {
    completeLoading.value = false;
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

watch(() => props.refreshKey, load);

onMounted(load);
</script>
