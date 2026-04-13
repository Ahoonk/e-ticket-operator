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
          class="w-full rounded-lg border border-sky-500/40 px-3 py-2 text-sm text-sky-200 hover:border-sky-400 sm:w-auto sm:py-1"
          @click="openExportModal"
        >
          Export PDF
        </button>
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
                <span class="text-slate-500">Keterangan:</span>
                <span class="ml-1 text-slate-200">{{ item.keterangan || '-' }}</span>
              </div>
              <div>
                <span class="text-slate-500">Tim:</span>
                <span class="ml-1 text-slate-200">{{ formatTeamMembers(item.tim_bertugas) || '-' }}</span>
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
              <template v-else>
                <button
                  v-if="canComplete && item.status === 'PROSES'"
                  type="button"
                  class="w-full rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
                  @click="openComplete(item)"
                >
                  Selesai
                </button>
              <button
                v-if="canUpload"
                type="button"
                class="inline-flex rounded-lg border border-sky-500/40 px-3 py-2 text-sm text-sky-200 hover:border-sky-400"
                @click="openDocumentModal(item)"
              >
                Upload Foto
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <teleport to="body">
      <div
        v-if="showExportModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 px-4"
        @click.self="closeExportModal"
      >
        <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-2xl">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-lg font-semibold text-white">Export PDF Kegiatan</h3>
              <p class="mt-1 text-sm text-slate-400">
                Pilih status kegiatan yang akan diekspor. Setiap kegiatan akan menjadi 1 halaman A4.
              </p>
            </div>
            <button
              type="button"
              class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-200 hover:border-slate-500"
              @click="closeExportModal"
            >
              Tutup
            </button>
          </div>

          <div v-if="exportError" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
            {{ exportError }}
          </div>

          <div class="mt-5 grid gap-4">
            <label class="grid gap-2 text-sm text-slate-300">
              Status yang diekspor
              <select v-model="exportStatusChoice" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2">
                <option v-for="option in exportOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </label>

            <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4 text-sm text-slate-300">
              <p class="font-medium text-white">Keterangan</p>
              <ul class="mt-2 grid gap-1 text-xs text-slate-400">
                <li>- <span class="text-slate-200">Belum Dikerjakan</span> = status belum dikerjakan</li>
                <li>- <span class="text-slate-200">Proses</span> = status proses</li>
                <li>- <span class="text-slate-200">Selesai</span> = status selesai</li>
                <li>- <span class="text-slate-200">Export Semua</span> = semua data yang tampil di akun ini</li>
              </ul>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-4">
              <button
                type="button"
                class="w-full rounded-lg bg-sky-500 px-5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-sky-400 sm:w-auto"
                @click="confirmExport"
              >
                {{ exportLoading ? 'Mengekspor...' : 'Export PDF' }}
              </button>
              <button
                type="button"
                class="w-full rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-200 hover:border-slate-500 sm:w-auto"
                @click="closeExportModal"
              >
                Batal
              </button>
            </div>
          </div>
        </div>
      </div>

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
              <p class="mt-2 text-sm text-white whitespace-pre-wrap">{{ formatTeamMembers(viewItem.tim_bertugas) || '-' }}</p>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
              <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Keterangan</p>
              <p class="mt-2 text-sm text-white whitespace-pre-wrap">{{ viewItem.keterangan || '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="showDocumentModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 px-4"
        @click.self="closeDocumentModal"
      >
        <div class="w-full max-w-2xl rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-2xl">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-lg font-semibold text-white">Upload Foto Dokumentasi</h3>
              <p class="mt-1 text-sm text-slate-400">
                File akan diunggah langsung ke storage server dan ditautkan ke kegiatan ini.
              </p>
            </div>
            <button
              type="button"
              class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-200 hover:border-slate-500"
              @click="closeDocumentModal"
            >
              Tutup
            </button>
          </div>

          <div v-if="documentError" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
            {{ documentError }}
          </div>

          <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
              <p class="text-sm font-medium text-white">Dokumen yang sudah diunggah</p>
              <div v-if="documentLoading" class="mt-3 text-sm text-slate-400">Memuat dokumen...</div>
              <div v-else-if="documentItems.length === 0" class="mt-3 text-sm text-slate-400">
                Belum ada dokumen untuk kegiatan ini.
              </div>
              <div v-else class="mt-3 grid gap-2">
                <a
                  v-for="doc in documentItems"
                  :key="doc.id"
                  :href="doc.drive_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="rounded-lg border border-slate-800 bg-slate-950/50 px-3 py-2 text-sm text-slate-200 hover:border-slate-700"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate font-medium text-white">{{ doc.original_name }}</p>
                      <p class="mt-1 text-xs text-slate-500">
                        {{ doc.uploader?.name || '-' }} • {{ doc.created_at || '-' }}
                      </p>
                    </div>
                    <span class="text-xs text-sky-200">Buka</span>
                  </div>
                </a>
              </div>
            </div>

            <form class="grid gap-4 rounded-xl border border-slate-800 bg-slate-900/50 p-4" @submit.prevent="submitDocument">
              <div class="grid gap-2">
                <label class="text-sm text-slate-300">File Foto</label>
                <input
                  ref="documentInputRef"
                  type="file"
                  accept="image/png,image/jpeg,image/jpg,image/webp"
                  class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-200"
                  @change="handleDocumentFile"
                  required
                />
              </div>
              <label class="grid gap-2 text-sm text-slate-300">
                Caption / Keterangan
                <textarea
                  v-model="documentCaption"
                  class="min-h-28 rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2"
                  placeholder="Opsional"
                />
              </label>

              <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-4">
                <button
                  type="submit"
                  class="inline-flex rounded-lg bg-sky-500 px-5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-sky-400"
                >
                  {{ documentUploading ? 'Mengunggah...' : 'Upload File' }}
                </button>
                <button
                  type="button"
                  class="inline-flex rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-200 hover:border-slate-500"
                  @click="closeDocumentModal"
                >
                  Batal
                </button>
              </div>
            </form>
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
import { listGangguanDokumen, uploadGangguanDokumen } from '../services/dokumen';

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
  canUpload: {
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
const showExportModal = ref(false);
const exportLoading = ref(false);
const exportError = ref(null);
const exportStatusChoice = ref('all');
const exportOptions = [
  { value: 'all', label: 'Export Semua' },
  { value: 'BELUM DIKERJAKAN', label: 'Belum Dikerjakan' },
  { value: 'PROSES', label: 'Proses' },
  { value: 'SELESAI', label: 'Selesai' },
];
const showViewModal = ref(false);
const viewItem = ref(null);
const showDocumentModal = ref(false);
const documentItem = ref(null);
const documentItems = ref([]);
const documentLoading = ref(false);
const documentUploading = ref(false);
const documentError = ref(null);
const documentCaption = ref('');
const documentInputRef = ref(null);
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
      formatTeamMembers(item.tim_bertugas),
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

const openExportModal = () => {
  exportError.value = null;
  exportStatusChoice.value = 'all';
  showExportModal.value = true;
};

const closeExportModal = () => {
  showExportModal.value = false;
  exportLoading.value = false;
  exportError.value = null;
  exportStatusChoice.value = 'all';
};

const exportItems = computed(() => {
  if (exportStatusChoice.value === 'all') {
    return [...items.value];
  }

  return items.value.filter((item) => (item.status || '').toUpperCase() === exportStatusChoice.value);
});

const normalizeText = (value) => {
  if (value === null || value === undefined || value === '') return '-';
  if (Array.isArray(value)) return value.join(', ');
  return String(value);
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

const loadImageAsDataUrl = async (url) => {
  const response = await fetch(url, { credentials: 'same-origin' });
  if (!response.ok) {
    throw new Error('Gagal memuat gambar.');
  }

  const blob = await response.blob();

  return await new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(new Error('Gagal membaca gambar.'));
    reader.readAsDataURL(blob);
  });
};

const getImageFormat = (mimeType, url) => {
  const normalizedMime = String(mimeType || '').toLowerCase();
  if (normalizedMime.includes('png')) return 'PNG';
  if (normalizedMime.includes('webp')) return 'WEBP';
  return 'JPEG';
};

const statusBadgeColor = (status) => {
  const value = (status || '').toUpperCase();
  if (value === 'SELESAI') return { fill: [34, 197, 94], text: [255, 255, 255] };
  if (value === 'PROSES') return { fill: [250, 204, 21], text: [0, 0, 0] };
  return { fill: [239, 68, 68], text: [255, 255, 255] };
};

const drawPdfPage = async (doc, item, documents, pageNumber, totalPages) => {
  const pageWidth = 210;
  const pageHeight = 297;
  const margin = 10;
  const contentWidth = pageWidth - margin * 2;
  const columnGap = 4;
  const columnWidth = (contentWidth - columnGap) / 2;
  const leftX = margin;
  const rightX = margin + columnWidth + columnGap;

  const titleColor = [0, 0, 0];
  const labelColor = [0, 0, 0];
  const bodyColor = [0, 0, 0];
  const borderColor = [0, 0, 0];

  const drawBox = (x, y, w, label, value, minLines = 1) => {
    const padding = 3;
    const labelH = 5;
    const fontSize = 11;
    const lineHeight = 4.1;
    doc.setFont('times', 'normal');
    doc.setFontSize(fontSize);
    const lines = doc.splitTextToSize(normalizeText(value), w - padding * 2);
    const actualLines = Math.max(lines.length, minLines);
    const boxH = labelH + 5 + actualLines * lineHeight + 4;

    doc.setDrawColor(...borderColor);
    doc.setLineWidth(0.4);
    doc.roundedRect(x, y, w, boxH, 3, 3);

  doc.setTextColor(...labelColor);
  doc.setFont('times', 'bold');
  doc.setFontSize(11);
  doc.text(label, x + padding, y + 5.5);

  doc.setTextColor(...bodyColor);
  doc.setFont('times', 'normal');
  doc.setFontSize(11);
  doc.text(lines, x + padding, y + 12);

    return boxH;
  };

  doc.setFillColor(255, 255, 255);
  doc.rect(0, 0, pageWidth, pageHeight, 'F');
  const badge = statusBadgeColor(item.status);
  const badgeLabel = item.status || 'BELUM DIKERJAKAN';
  const badgeWidth = Math.max(34, doc.getTextWidth(badgeLabel) + 16);

  let y = margin;

  const titleBoxH = 18;
  doc.setDrawColor(...borderColor);
  doc.setLineWidth(0.4);
  doc.roundedRect(margin, y, contentWidth, titleBoxH, 3, 3);
  doc.setTextColor(...labelColor);
  doc.setFont('times', 'bold');
  doc.setFontSize(11);
  doc.text('Judul', margin + 3, y + 5.5);
  doc.setFont('times', 'normal');
  doc.text(normalizeText(item.jenis_gangguan), margin + 3, y + 11.5);

  const badgeX = pageWidth - margin - badgeWidth - 3;
  const badgeY = y + 4;
  doc.setFillColor(...badge.fill);
  doc.roundedRect(badgeX, badgeY, badgeWidth, 10, 5, 5, 'F');
  doc.setTextColor(...badge.text);
  doc.setFont('times', 'bold');
  doc.setFontSize(12);
  doc.text(badgeLabel, badgeX + 8, badgeY + 7);
  y += titleBoxH + 4;

  const row1 = Math.max(
    drawBox(leftX, y, columnWidth, 'Tanggal Selesai', item.selesai_pengerjaan, 1),
    drawBox(rightX, y, columnWidth, 'Lokasi/OPD', item.lokasi_opd, 2),
  );
  y += row1 + 4;

  const row2 = Math.max(
    drawBox(leftX, y, columnWidth, 'Mulai Pengerjaan', item.mulai_pengerjaan, 1),
    drawBox(rightX, y, columnWidth, 'Status', item.status, 1),
  );
  y += row2 + 4;

  y += drawBox(margin, y, contentWidth, 'Kendala', item.kendala, 2) + 3;
  y += drawBox(margin, y, contentWidth, 'Tindak Lanjut/Solusi', item.tindak_lanjut, 2) + 3;
  y += drawBox(margin, y, contentWidth, 'Tim Bertugas', formatTeamMembers(item.tim_bertugas), 2) + 3;
  drawBox(margin, y, contentWidth, 'Keterangan', item.keterangan, 2);

  const uploadedDocuments = Array.isArray(documents) ? documents : [];
  if (uploadedDocuments.length > 0) {
    const galleryStartY = y + 6;
    const galleryColumns = uploadedDocuments.length > 4 ? 4 : 3;
    const galleryGap = 3;
    const galleryRows = Math.ceil(uploadedDocuments.length / galleryColumns);
    const availableHeight = pageHeight - galleryStartY - margin - 10;
    const cellHeight = Math.max(
      16,
      Math.floor((availableHeight - 7 - (galleryRows - 1) * galleryGap) / galleryRows),
    );
    const cellWidth = (contentWidth - (galleryColumns - 1) * galleryGap) / galleryColumns;

    doc.setTextColor(...labelColor);
    doc.setFont('times', 'bold');
    doc.setFontSize(11);
    doc.text('Dokumentasi Foto', margin, galleryStartY);

    let loadErrorCount = 0;
    for (let index = 0; index < uploadedDocuments.length; index += 1) {
      const documentItem = uploadedDocuments[index];
      const row = Math.floor(index / galleryColumns);
      const col = index % galleryColumns;
      const cellX = margin + col * (cellWidth + galleryGap);
      const cellY = galleryStartY + 5 + row * (cellHeight + galleryGap);
      const imagePadding = 1.8;
      const imageBoxHeight = Math.max(8, cellHeight - 7);
      const imageBoxWidth = cellWidth - imagePadding * 2;
      const imageX = cellX + imagePadding;
      const imageY = cellY + imagePadding;

      doc.setDrawColor(...borderColor);
      doc.setLineWidth(0.25);
      doc.roundedRect(cellX, cellY, cellWidth, cellHeight, 2, 2);

      try {
        const dataUrl = await loadImageAsDataUrl(documentItem.drive_url);
        const imageFormat = getImageFormat(documentItem.mime_type, documentItem.drive_url);
        const props = doc.getImageProperties(dataUrl);
        const ratio = Math.min(imageBoxWidth / props.width, imageBoxHeight / props.height);
        const drawWidth = props.width * ratio;
        const drawHeight = props.height * ratio;
        const offsetX = imageX + (imageBoxWidth - drawWidth) / 2;
        const offsetY = imageY + (imageBoxHeight - drawHeight) / 2;
        doc.addImage(dataUrl, imageFormat, offsetX, offsetY, drawWidth, drawHeight);
      } catch (err) {
        loadErrorCount += 1;
        doc.setFont('times', 'italic');
        doc.setFontSize(9);
        doc.setTextColor(120, 120, 120);
        doc.text('Foto tidak dapat dimuat', cellX + 3, cellY + cellHeight / 2);
      }

      doc.setTextColor(...labelColor);
      doc.setFont('times', 'normal');
      doc.setFontSize(8);
      const caption = doc.splitTextToSize(normalizeText(documentItem.original_name), cellWidth - 4);
      doc.text(caption.slice(0, 2), cellX + 2, cellY + cellHeight - 2);
    }

    if (loadErrorCount > 0) {
      doc.setFont('times', 'italic');
      doc.setFontSize(8);
      doc.setTextColor(120, 120, 120);
      doc.text(`Sebagian foto tidak dapat dimuat (${loadErrorCount}).`, margin, pageHeight - 14);
    }
  }

  doc.setTextColor(...labelColor);
  doc.setFont('times', 'normal');
  doc.setFontSize(11);
  doc.text(`Dicetak pada ${new Date().toLocaleString('id-ID')}`, margin, pageHeight - 10);
};

const confirmExport = async () => {
  exportLoading.value = true;
  exportError.value = null;

  try {
    const rows = exportItems.value;
    if (!rows.length) {
      exportError.value = 'Tidak ada data kegiatan untuk status yang dipilih.';
      return;
    }

    const { jsPDF } = await import('jspdf');
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const documentsByGangguan = new Map();

    await Promise.all(rows.map(async (item) => {
      try {
        documentsByGangguan.set(item.id, await listGangguanDokumen(item.id));
      } catch (err) {
        documentsByGangguan.set(item.id, []);
      }
    }));

    for (const [index, item] of rows.entries()) {
      if (index > 0) {
        doc.addPage();
      }
      await drawPdfPage(doc, item, documentsByGangguan.get(item.id) || [], index + 1, rows.length);
    }

    const suffix = exportStatusChoice.value === 'all' ? 'semua' : exportStatusChoice.value.toLowerCase().replace(/\s+/g, '-');
    doc.save(`kegiatan-${suffix}.pdf`);
    closeExportModal();
  } catch (err) {
    exportError.value = getErrorMessage(err, 'Gagal mengekspor PDF.');
  } finally {
    exportLoading.value = false;
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

const loadDocuments = async (gangguanId) => {
  if (!gangguanId) {
    documentItems.value = [];
    return;
  }

  documentLoading.value = true;
  documentError.value = null;
  try {
    documentItems.value = await listGangguanDokumen(gangguanId);
  } catch (err) {
    documentError.value = getErrorMessage(err, 'Gagal memuat dokumen.');
  } finally {
    documentLoading.value = false;
  }
};

const openDocumentModal = async (item) => {
  documentItem.value = item;
  documentCaption.value = '';
  documentItems.value = [];
  documentError.value = null;
  showDocumentModal.value = true;
  await loadDocuments(item.id);
};

const closeDocumentModal = () => {
  showDocumentModal.value = false;
  documentItem.value = null;
  documentItems.value = [];
  documentLoading.value = false;
  documentUploading.value = false;
  documentError.value = null;
  documentCaption.value = '';
  if (documentInputRef.value) {
    documentInputRef.value.value = '';
  }
};

const handleDocumentFile = (event) => {
  const [file] = event.target.files || [];
  documentError.value = null;

  if (!file) return;

  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    documentError.value = 'File harus berupa foto JPG, PNG, atau WEBP.';
    event.target.value = '';
  }
};

const submitDocument = async () => {
  if (!documentItem.value) return;
  const input = documentInputRef.value;
  const file = input?.files?.[0];

  if (!file) {
    documentError.value = 'Pilih file foto terlebih dahulu.';
    return;
  }

  documentUploading.value = true;
  documentError.value = null;

  try {
    const formData = new FormData();
    formData.append('file', file);
    if (documentCaption.value.trim()) {
      formData.append('caption', documentCaption.value.trim());
    }

    await uploadGangguanDokumen(documentItem.value.id, formData);
    documentCaption.value = '';
    if (input) {
      input.value = '';
    }
    await loadDocuments(documentItem.value.id);
    await load();
  } catch (err) {
    documentError.value = getErrorMessage(err, 'Gagal mengunggah dokumen.');
  } finally {
    documentUploading.value = false;
  }
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
