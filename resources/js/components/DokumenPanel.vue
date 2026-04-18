<template>
  <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-white">Dokumentasi Pekerjaan</h2>
        <p class="mt-1 text-sm text-slate-400">
          Semua file foto dokumentasi yang sudah diunggah oleh user.
        </p>
      </div>
      <button
        class="w-full rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500 sm:w-auto"
        @click="load"
      >
        Refresh
      </button>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <input
        v-model="search"
        class="w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 sm:w-80"
        placeholder="Cari kegiatan, tim, nama file, pengunggah..."
      />
      <p class="text-xs text-slate-400">Total file: {{ filteredGroups.length }}</p>
    </div>

    <div v-if="loading" class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/40 p-6 text-sm text-slate-300">
      Memuat dokumen...
    </div>

    <div v-else class="mt-5 grid gap-4">
      <div v-if="filteredGroups.length === 0" class="rounded-2xl border border-dashed border-slate-800 bg-slate-950/30 p-6 text-center text-sm text-slate-400">
        Belum ada dokumen yang diunggah.
      </div>

      <article
        v-for="group in filteredGroups"
        :key="group.key"
        class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4 sm:p-5"
      >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-base font-semibold text-white">{{ group.gangguan.jenis_gangguan || 'Tanpa judul' }}</h3>
              <span class="rounded-full border border-slate-700 px-2 py-0.5 text-[10px] text-slate-300">
                {{ group.documents.length }} file
              </span>
            </div>
            <p class="mt-1 text-sm text-slate-400">
              {{ group.gangguan.lokasi_opd || '-' }} - {{ formatDateTime(group.gangguan.tanggal_gangguan) }}
            </p>
            <p class="mt-2 text-xs text-slate-500">
              Tim: {{ formatTeamMembers(group.gangguan.tim_bertugas) || '-' }}
            </p>
          </div>
          <div class="rounded-xl border border-slate-800 bg-slate-900/60 px-3 py-2 text-xs text-slate-400">
            Status: <span class="text-slate-200">{{ group.gangguan.status || '-' }}</span>
          </div>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
          <article
            v-for="doc in group.documents"
            :key="doc.id"
            class="rounded-xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-slate-700"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-white">{{ doc.original_name }}</p>
                <p class="mt-1 text-xs text-slate-500">
                  Diunggah {{ formatDateTime(doc.created_at) }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <a
                  :href="doc.drive_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="rounded-full border border-emerald-500/30 px-2 py-0.5 text-[10px] text-emerald-200 transition hover:border-emerald-400"
                >
                  Lihat
                </a>
                <button
                  v-if="canDelete"
                  type="button"
                  class="rounded-full border border-rose-500/40 px-2 py-0.5 text-[10px] text-rose-200 transition hover:border-rose-400 disabled:opacity-50"
                  :disabled="deletingId === doc.id"
                  @click="removeDoc(doc)"
                >
                  {{ deletingId === doc.id ? 'Hapus...' : 'Hapus' }}
                </button>
              </div>
            </div>

            <div class="mt-3 grid gap-1 text-xs text-slate-400">
              <p>Pengunggah: <span class="text-slate-200">{{ doc.uploader?.name || '-' }}</span></p>
              <p>Caption: <span class="text-slate-200">{{ doc.caption || '-' }}</span></p>
            </div>
          </article>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { deleteDokumen, listDokumen } from '../services/kegiatan-jaringan-dokumen';
import { formatDateTime } from '../utils/datetime';

const props = defineProps({
  canDelete: {
    type: Boolean,
    default: false,
  },
});

const groups = ref([]);
const loading = ref(false);
const error = ref(null);
const search = ref('');
const deletingId = ref(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    const items = await listDokumen();
    const grouped = new Map();

    items.forEach((item) => {
      const key = item.gangguan_id;
      if (!grouped.has(key)) {
        grouped.set(key, {
          key,
          gangguan: item.gangguan || {},
          documents: [],
        });
      }

      grouped.get(key).documents.push(item);
    });

    groups.value = [...grouped.values()];
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat dokumen.');
  } finally {
    loading.value = false;
  }
};

const removeDoc = async (doc) => {
  if (!confirm(`Hapus dokumen "${doc.original_name}"?`)) return;

  deletingId.value = doc.id;
  error.value = null;
  try {
    await deleteDokumen(doc.id);
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menghapus dokumen.');
  } finally {
    deletingId.value = null;
  }
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

const filteredGroups = computed(() => {
  const term = search.value.trim().toLowerCase();
  if (!term) return groups.value;

  return groups.value.filter((group) => {
    const haystack = [
      group.gangguan.jenis_gangguan,
      group.gangguan.lokasi_opd,
      group.gangguan.status,
      group.gangguan.tim_bertugas,
      ...group.documents.map((doc) => [
        doc.original_name,
        doc.caption,
        doc.uploader?.name,
      ].join(' ')),
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase();

    return haystack.includes(term);
  });
});

onMounted(load);
</script>
